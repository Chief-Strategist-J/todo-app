<?php

namespace App\Models;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use function App\Helper\successMessage;

class Todo extends Model
{
    use HasFactory, SoftDeletes;
    const cacheKeyForTodoList = 'todoList';

    protected $fillable = [
        'title',
        'description',
        'is_completed',
        'due_date',
        'priority',
        'assigned_to',
        'tags',
        'created_by',
        'updated_by',
        'status',
        'reminder',
        'attachment',
        'category',
        'estimated_time',
        'actual_time',
        'location',
        'recurring',
        'recurring_frequency',
        'notes',
        'completed_at',
        'color_code',
        'is_archived',
        'firebase_todo_id'
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'is_completed' => 'boolean',
        'due_date' => 'datetime',
        'priority' => 'string',
        'assigned_to' => 'integer',
        'tags' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status' => 'string',
        'reminder' => 'datetime',
        'attachment' => 'string',
        'category' => 'string',
        'estimated_time' => 'integer',
        'actual_time' => 'integer',
        'location' => 'string',
        'recurring' => 'boolean',
        'recurring_frequency' => 'string',
        'notes' => 'string',
        'completed_at' => 'datetime',
        'color_code' => 'string',
        'is_archived' => 'boolean',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    private function processTodoFields(Request $request, Todo $todo): void
    {
        $inputFields = [
            'title', 'description', 'notes', 'due_date', 'priority', 'assigned_to',
            'tags', 'created_by', 'updated_by', 'status', 'reminder', 'attachment',
            'category', 'estimated_time', 'actual_time', 'location', 'recurring',
            'recurring_frequency', 'firebase_todo_id', 'completed_at', 'color_code',
            'is_archived'
        ];

        foreach ($inputFields as $field) {
            if ($request->has($field)) {
                $todo->$field = $request->input($field);
            }
        }
    }
    private function clearTodoListCache(): void
    {
        Cache::forget(Todo::cacheKeyForTodoList);
    }

    public function updateTodo(UpdateTodoRequest $request): JsonResponse
    {
        $todo = Todo::find($request->input("todo_id"));

        if (is_null($todo)) return successMessage(data: ['message' => "todo id does not exist"]);

        $this->processTodoFields($request, $todo);

        $this->clearTodoListCache();

        $todo->update();

        return successMessage(data: $todo);
    }

    public function createTodo(StoreTodoRequest $request): JsonResponse
    {
        $todo = new Todo();

        $this->processTodoFields($request, $todo);

        $todo->save();

        $this->clearTodoListCache();

        return successMessage(
            data: [
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'notes' => $todo->notes
            ]
        );
    }

    public function getTodoList(): Collection
    {
        $minutesInWeek = 7 * 24 * 60;

        $fields = ['id', 'title', 'description', 'notes', 'firebase_todo_id'];

        return Cache::remember(Todo::cacheKeyForTodoList, $minutesInWeek, function () use ($fields) {
            return Todo::select($fields)->get();
        });
    }
}
