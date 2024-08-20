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

}
