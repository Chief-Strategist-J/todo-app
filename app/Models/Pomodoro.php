<?php

namespace App\Models;

use App\Http\Requests\CreateBulkPomodorosRequest;
use App\Jobs\ProcessExpiredTimers;
use Carbon\Carbon;
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


    public function create_bulk_pomodoros(CreateBulkPomodorosRequest $request): \Illuminate\Http\JsonResponse
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

    public function createBulkPomodoros(CreateBulkPomodorosRequest $request): \Illuminate\Http\JsonResponse
    {
        $title = $request->input('title');
        $duration = $request->input('duration');
        $status = $request->input('status', 'pending');
        $todoId = $request->input('todo_id');
        $userId = $request->input('user_id');
        $numberOfPomodoros = $request->input('number_of_pomodoros', 1);

        DB::beginTransaction();

        try {
            $uuid = (string) Str::uuid();
            $pomodoro = [
                'uuid' => $uuid,
                'title' => $title,
                'duration' => $duration,
                'status' => $status,
                'todo_id' => $todoId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $pomodoroId = DB::table('pomodoros')->insertGetId($pomodoro);

            $timers = array_fill(0, $numberOfPomodoros, [
                'pomodoro_id' => $pomodoroId,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('pomodoro_timers')->insert($timers);

            $result = [
                'pomodoro' => DB::table('pomodoros')->select('uuid', 'user_id', 'todo_id', 'id')->find($pomodoroId),
                'timers' => DB::table('pomodoro_timers')->select('id', 'pomodoro_id')->where('pomodoro_id', $pomodoroId)->get()
            ];

            DB::commit();
            Log::info("Created pomodoro with ID: {$pomodoroId} and {$numberOfPomodoros} timers");
            return response()->json($result, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bulk pomodoros: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred.'], 500);
        }
    }

    public function startPomodoro(int $pomodoroId): void
    {
        try {
            $now = now();

            DB::transaction(function () use ($pomodoroId, $now) {
                $pomodoroData = DB::table('pomodoros')
                    ->leftJoin('pomodoro_timers', 'pomodoros.id', '=', 'pomodoro_timers.pomodoro_id')
                    ->where('pomodoros.id', $pomodoroId)
                    ->whereNull('pomodoros.deleted_at')
                    ->whereNull('pomodoro_timers.completed_at')
                    ->where('pomodoro_timers.status', 'pending')
                    ->select(
                        'pomodoros.*',
                        'pomodoro_timers.id as timer_id',
                        'pomodoro_timers.status as timer_status'
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$pomodoroData) {
                    Log::info("Pomodoro {$pomodoroId} not found or no available timers.");
                    return null;
                }

                // Update the Pomodoro status
                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'in_progress',
                        'start_time' => $now,
                        'updated_at' => $now,
                    ]);

                DB::table('pomodoro_timers')
                    ->where('id', $pomodoroData->timer_id)
                    ->whereNull('completed_at')
                    ->update([
                        'status' => 'in_progress', // Changed status to 'in_progress'
                        'updated_at' => $now,
                        'started_at' => $now,
                    ]);

                $this->scheduleTimerCompletion($pomodoroData->timer_id, $pomodoroData->duration);
                Log::info("Started pomodoro {$pomodoroId} with timer {$pomodoroData->timer_id}");
            });

           
        } catch (\Exception $e) {
            Log::error('Error starting pomodoro: ' . $e->getMessage());
        }
    }

    public function stopPomodoro(int $pomodoroId): void
    {
        try {
            $now = now();
            DB::transaction(function () use ($pomodoroId, $now) {
                $pomodoroData = DB::table('pomodoros')
                    ->leftJoin('pomodoro_timers', 'pomodoros.id', '=', 'pomodoro_timers.pomodoro_id')
                    ->where('pomodoros.id', $pomodoroId)
                    ->whereNull('pomodoros.deleted_at')
                    ->whereNull('pomodoro_timers.completed_at')
                    ->whereIn('pomodoro_timers.status',  ['pending', 'in_progress', 'paused'])
                    ->select(
                        'pomodoros.*',
                        'pomodoro_timers.id as timer_id',
                        'pomodoro_timers.status as timer_status'
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$pomodoroData) {
                    Log::info("Pomodoro {$pomodoroId} not found or not in a stoppable state.");
                    return null;
                }

                // Update the Pomodoro status
                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'stopped',
                        'updated_at' => $now,
                    ]);

                DB::table('pomodoro_timers')
                    ->where('id', $pomodoroData->timer_id)
                    ->update([
                        'status' => 'stopped',
                        'updated_at' => $now,
                        'stopped_at' => $now,
                    ]);

                Log::info("Stopped pomodoro {$pomodoroId} with timer {$pomodoroData->timer_id}");
            });

        } catch (\Exception $e) {
            Log::error('Error stopping pomodoro: ' . $e->getMessage());
        }
    }

    public function resumePomodoro(int $pomodoroId): void
    {
        try {
            $now = now();
            DB::transaction(function () use ($pomodoroId, $now) {
                $pomodoroData = DB::table('pomodoros')
                    ->leftJoin('pomodoro_timers', 'pomodoros.id', '=', 'pomodoro_timers.pomodoro_id')
                    ->where('pomodoros.id', $pomodoroId)
                    ->whereNull('pomodoros.deleted_at')
                    ->whereNull('pomodoro_timers.completed_at')
                    ->where('pomodoro_timers.status', 'stopped')
                    ->select(
                        'pomodoros.*',
                        'pomodoro_timers.id as timer_id',
                        'pomodoro_timers.status as timer_status'
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$pomodoroData) {
                    Log::info("Pomodoro {$pomodoroId} not found or not paused.");
                    return null;
                }

                // Update the Pomodoro status
                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'in_progress',
                        'updated_at' => $now,
                    ]);

                DB::table('pomodoro_timers')
                    ->where('id', $pomodoroData->timer_id)
                    ->update([
                        'status' => 'in_progress', // Changed status to 'pending' or any other appropriate status
                        'updated_at' => $now,
                        'resumed_at' => $now,
                    ]);

                $this->scheduleTimerCompletion($pomodoroData->timer_id, $pomodoroData->duration);

                Log::info("Resumed pomodoro {$pomodoroId} with timer {$pomodoroData->timer_id}");
            });
        } catch (\Exception $e) {
            Log::error('Error resuming pomodoro: ' . $e->getMessage());
        }
    }


    public function endPomodoro(int $pomodoroId): void
    {
        try {
            $now = now();

            $result = DB::transaction(function () use ($pomodoroId, $now) {
                $pomodoroData = DB::table('pomodoros')
                    ->leftJoin('pomodoro_timers', 'pomodoros.id', '=', 'pomodoro_timers.pomodoro_id')
                    ->where('pomodoros.id', $pomodoroId)
                    ->whereNull('pomodoros.deleted_at')
                    ->whereNull('pomodoro_timers.completed_at')
                    ->whereIn('pomodoro_timers.status', ['pending', 'in_progress', 'paused']) // Handle various statuses
                    ->select(
                        'pomodoros.*',
                        'pomodoro_timers.id as timer_id',
                        'pomodoro_timers.status as timer_status'
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$pomodoroData) {
                    Log::info("Pomodoro {$pomodoroId} not found or already completed.");
                    return null;
                }

                // Update the Pomodoro status
                DB::table('pomodoros')
                    ->where('id', $pomodoroId)
                    ->update([
                        'status' => 'completed',
                        'end_time' => $now,
                        'updated_at' => $now,
                    ]);

                DB::table('pomodoro_timers')
                    ->where('id', $pomodoroData->timer_id)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => $now,
                        'updated_at' => $now,
                    ]);

                Log::info("Ended pomodoro {$pomodoroId} with timer {$pomodoroData->timer_id}");
            });
        } catch (\Exception $e) {
            Log::error('Error ending pomodoro: ' . $e->getMessage());
        }
    }


    private function scheduleTimerCompletion(int $timerId, int $durationInMinutes): void
    {
        $delayInSeconds = $durationInMinutes * 60;
        ProcessExpiredTimers::dispatch($timerId)->delay($delayInSeconds);
        Log::info("Scheduled timer completion for timer {$timerId} after {$delayInSeconds} seconds");
    }


    public function getPomodoroStats(int $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $pomodoroStats = DB::table('pomodoros')
                ->leftJoin('pomodoro_timers', 'pomodoros.id', '=', 'pomodoro_timers.pomodoro_id')
                ->where('pomodoros.user_id', $userId)
                ->whereNull('pomodoros.deleted_at')
                ->whereNull('pomodoro_timers.completed_at')
                ->select(
                    'pomodoros.id as pomodoro_id',
                    'pomodoros.status as pomodoro_status',
                    'pomodoros.duration as pomodoro_duration',
                    DB::raw('JSON_ARRAYAGG(
                    JSON_OBJECT(
                        "timer_id", pomodoro_timers.id,
                        "timer_status", pomodoro_timers.status,
                        "started_at", CASE WHEN pomodoro_timers.started_at IS NOT NULL THEN pomodoro_timers.started_at ELSE NULL END,
                        "stopped_at", CASE WHEN pomodoro_timers.stopped_at IS NOT NULL THEN pomodoro_timers.stopped_at ELSE NULL END,
                        "resumed_at", CASE WHEN pomodoro_timers.resumed_at IS NOT NULL THEN pomodoro_timers.resumed_at ELSE NULL END
                    )
                ) as timers')
                )
                ->groupBy('pomodoros.id')
                ->get();

            // Decode JSON and filter out null fields
            $result = $pomodoroStats->map(function ($pomodoro) {
                $timers = json_decode($pomodoro->timers, true);

                $pomodoro->timers = collect($timers)->map(function ($timer) {
                    return array_filter($timer, function ($value) {
                        return $value !== null;
                    });
                })->values();

                return $pomodoro;
            });

            Log::info("Retrieved pomodoro stats for user {$userId}");
            return response()->json([
                'total_pomodoros' => $result->count(),
                'pomodoros' => $result->values(),
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error retrieving pomodoro stats for user {$userId}: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving pomodoro stats.'], 500);
        }
    }

}
