<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class Pomodoro extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pomodoros';

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'duration',
        'status',
        'start_time',
        'end_time',
        'metadata',
        'priority',
        'tags',
        'is_completed',
        'is_archived',
        'todo_id',
        'user_id',
        'project_id',
    ];

    protected $casts = [
        'uuid' => 'string',
        'title' => 'string',
        'description' => 'string',
        'duration' => 'integer',
        'status' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'metadata' => 'array',
        'priority' => 'string',
        'tags' => 'string',
        'is_completed' => 'boolean',
        'is_archived' => 'boolean',
        'todo_id' => 'integer',
        'user_id' => 'integer',
        'project_id' => 'integer',
    ];

    // Define the relationship with the User model through the pivot table
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pomodoro_user')->withPivot(['todo_id', 'assigned_duration', 'completed_at', 'role', 'is_active', 'is_completed'])->withTimestamps();
    }

    public function todo()
    {
        return $this->belongsTo(Todo::class);
    }


    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'pomodoro_tag')
            ->withPivot('tag_type');
    }


    public function create_bulk_pomodoros(Request $request): \Illuminate\Http\JsonResponse
    {
        $title = $request->input('title');
        $duration = $request->input('duration');
        $status = $request->input('status', 'pending');
        $todoId = $request->input('todo_id');
        $userId = $request->input('user_id');
        $numberOfPomodoros = $request->input('number_of_pomodoros', 1);

        DB::beginTransaction();

        try {

            $pomodoros = [];
            $uuids = [];

            for ($i = 0; $i < $numberOfPomodoros; $i++) {
                $uuid = (string) Str::uuid();
                $uuids[] = $uuid;
                $pomodoros[] = [
                    'uuid' => $uuid,
                    'title' => $title,
                    'duration' => $duration,
                    'status' => $status,
                    'todo_id' => $todoId,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }


            DB::table('pomodoros')->insert($pomodoros);

            $pomodoros = DB::table('pomodoros')
                ->select('id', 'uuid', 'title', 'duration', 'status', 'todo_id', 'user_id', 'created_at', 'updated_at')
                ->whereIn('uuid', $uuids)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();

            DB::commit();
            return response()->json($pomodoros, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }

    public function startPomodoro(int $pomodoroId): void
    {
        try {
            DB::beginTransaction();

            $existingTimer = DB::table('pomodoro_timers')
                ->where('pomodoro_id', $pomodoroId)
                ->whereNull('completed_at')
                ->first();

            if ($existingTimer) {
                if ($existingTimer->status === 'paused' || $existingTimer->status === 'interrupted') {
                    DB::table('pomodoro_timers')
                        ->where('id', $existingTimer->id)
                        ->update([
                            'resumed_at' => now(),
                            'status' => 'started',
                            'segment_duration_seconds' => $existingTimer->segment_duration_seconds,
                            'pause_duration_seconds' => $existingTimer->pause_duration_seconds,
                            'active_duration_seconds' => $existingTimer->active_duration_seconds,
                            'is_interrupted' => false,
                        ]);
                } else {
                    DB::rollBack();
                    return;
                }
            } else {
                DB::table('pomodoro_timers')->insert([
                    'pomodoro_id' => $pomodoroId,
                    'started_at' => now(),
                    'status' => 'started',
                    'segment_duration_seconds' => 0,
                    'pause_duration_seconds' => 0,
                    'active_duration_seconds' => 0,
                    'is_interrupted' => false,
                    'number_of_pauses' => 0,
                    'device_used' => null,
                    'user_feedback' => null,
                    'performance_score' => null,
                ]);
            }

            DB::table('pomodoros')->where('id', $pomodoroId)->update([
                'status' => 'in_progress',
                'start_time' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting pomodoro: ' . $e->getMessage());
        }
    }


    public function stopPomodoro(int $pomodoroId): void
    {
        $now = now();

        try {
            DB::transaction(function () use ($pomodoroId, $now) {

                $timer = DB::table('pomodoro_timers')
                    ->select('id', 'started_at', 'pause_duration_seconds', 'active_duration_seconds')
                    ->where('pomodoro_id', $pomodoroId)
                    ->whereNull('completed_at')
                    ->whereNull('resumed_at')
                    ->whereNull('stopped_at')
                    ->first();


                if (!$timer) {
                    return;
                }


                $segmentDuration = $timer->started_at ? $now->diffInSeconds($timer->started_at) : 0;
                $updatedPauseDuration = $timer->pause_duration_seconds + $segmentDuration;
                $updatedActiveDuration = $timer->active_duration_seconds + $segmentDuration;


                DB::table('pomodoro_timers')
                    ->where('id', $timer->id)
                    ->update([
                        'stopped_at' => $now,
                        'status' => 'stopped',
                        'segment_duration_seconds' => $segmentDuration,
                        'pause_duration_seconds' => $updatedPauseDuration,
                        'active_duration_seconds' => $updatedActiveDuration,
                    ]);


                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'paused',
                        'end_time' => $now,
                    ]);
            });
        } catch (\Exception $e) {
            Log::error('Error stopping pomodoro: ' . $e->getMessage());
        }
    }


    public function resumePomodoro(int $pomodoroId): void
    {
        $now = now();

        try {
            DB::transaction(function () use ($pomodoroId, $now) {

                $timer = DB::table('pomodoro_timers')
                    ->select('id', 'started_at', 'stopped_at', 'segment_duration_seconds', 'pause_duration_seconds', 'active_duration_seconds')
                    ->where('pomodoro_id', $pomodoroId)
                    ->whereNull('completed_at')
                    ->whereNotNull('stopped_at')
                    ->whereNull('resumed_at')
                    ->first();

                if (!$timer) {
                    return;
                }


                $pauseDuration = $now->diffInSeconds($timer->stopped_at);


                DB::table('pomodoro_timers')
                    ->where('id', $timer->id)
                    ->update([
                        'resumed_at' => $now,
                        'status' => 'started',
                        'pause_duration_seconds' => $timer->pause_duration_seconds + $pauseDuration,
                        'segment_duration_seconds' => $timer->segment_duration_seconds,
                        'active_duration_seconds' => $timer->active_duration_seconds,
                    ]);


                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'in progress',
                        'start_time' => $now,
                    ]);
            });
        } catch (\Exception $e) {
            Log::error('Error resuming pomodoro: ' . $e->getMessage());
        }
    }




    public function endPomodoro(int $pomodoroId): void
    {
        $now = now();

        try {
            DB::transaction(function () use ($pomodoroId, $now) {
                $timer = DB::table('pomodoro_timers')
                    ->select('id', 'started_at', 'stopped_at', 'resumed_at', 'segment_duration_seconds', 'pause_duration_seconds', 'active_duration_seconds')
                    ->where('pomodoro_id', $pomodoroId)
                    ->whereNull('completed_at')
                    ->first();

                if (!$timer) {
                    return;
                }

                $startTime = $timer->started_at;
                $endTime = $now;

                $activeDuration = $endTime->diffInSeconds($startTime);

                $totalDuration = $activeDuration + $timer->pause_duration_seconds;

                DB::table('pomodoro_timers')
                    ->where('id', $timer->id)
                    ->update([
                        'completed_at' => $now,
                        'status' => 'completed',
                        'segment_duration_seconds' => $totalDuration,
                        'duration_in_seconds' => $totalDuration,
                    ]);


                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'completed',
                        'end_time' => $now,
                    ]);
            });
        } catch (\Exception $e) {
            Log::error('Error ending pomodoro: ' . $e->getMessage());
        }
    }


    public function getPomodoroStats(int $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $pomodoros = DB::table('pomodoros')
                ->join('pomodoro_timers', 'pomodoros.id', '=', 'pomodoro_timers.pomodoro_id')
                ->where('pomodoros.user_id', $userId)
                ->whereNull('pomodoros.deleted_at')
                ->where('pomodoros.status', '!=', 'completed')
                ->select(
                    'pomodoros.id',
                    'pomodoros.duration',
                    'pomodoro_timers.started_at',
                    'pomodoro_timers.stopped_at',
                    'pomodoro_timers.resumed_at',
                    'pomodoro_timers.pause_duration_seconds',
                    'pomodoro_timers.segment_duration_seconds'
                )
                ->get();

            $pomodoroCount = $pomodoros->count();
            $totalRemainingDuration = $pomodoros->sum(function ($pomodoro) {
                $now = now();

                if ($pomodoro->stopped_at) {
                    return max(0, $pomodoro->duration - $pomodoro->segment_duration_seconds);
                }

                if ($pomodoro->started_at) {
                    $elapsed = $now->diffInSeconds($pomodoro->started_at);
                    $activeDuration = $elapsed - $pomodoro->pause_duration_seconds;
                    $remainingDuration = $pomodoro->duration - $activeDuration;
                    return max(0, $remainingDuration);
                }


                return $pomodoro->duration;
            });

            return response()->json([
                'pomodoro_count' => $pomodoroCount,
                'total_remaining_duration' => $totalRemainingDuration,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving pomodoro stats: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving pomodoro stats.'], 500);
        }
    }



}
