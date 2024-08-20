<?php

namespace App\Models;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Jobs\DeleteExpiredTodoJob;
use App\Jobs\FetchPaginatedTodos;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

use function App\Helper\getIndianTime;
use function App\Helper\successMessage;

class Todo extends Model
{
    use HasFactory, SoftDeletes;


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
        'firebase_todo_id',
        'start_time',
        'end_time',
        'date'
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'is_completed' => 'boolean',
        'due_date' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'date' => 'datetime',
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

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    private function processTodoFields(Request $request, Todo $todo): void
    {
        $inputFields = [
            'title',
            'description',
            'notes',
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
            'firebase_todo_id',
            'completed_at',
            'color_code',
            'is_archived',
            'is_completed',
            'start_time',
            'end_time',
            'date',
        ];

        foreach ($inputFields as $field) {
            if ($request->has($field)) {
                $todo->$field = $request->input($field);
                Log::debug('An informational message.' . $todo->$field);
            }
        }
    }




    public function updateTodo(UpdateTodoRequest $request): JsonResponse
    {
        $todo = Todo::find($request->input("todo_id"));

        if (is_null($todo))
            return successMessage(data: ['message' => "todo id does not exist"]);

        $this->processTodoFields($request, $todo);

        $todo->update();

        return successMessage(data: $todo);
    }

    public function createTodo(StoreTodoRequest $request): Todo
    {
        $todo = new Todo();

        $this->processTodoFields($request, $todo);

        $todo->save();

        if ($request->input('is_want_to_delete_todo_at_end_time')) {
            DeleteExpiredTodoJob::dispatch($todo->id)->delay(getIndianTime($request->input('end_time')));
        }

        return $todo;
    }

    public function getTodoList(): Collection
    {
        $fields = [
            'todos.id',
            'todos.title',
            'todos.notes',
            'todos.created_by',
            'todos.firebase_todo_id',
            'todos.start_time',
            'todos.end_time',
            'todos.date',
            'todos.description',
            'todos.priority'
        ];

        // Query to get todos with tags names, and apply filters
        $todos = Todo::select($fields)
            ->leftJoin('tag_todo', 'todos.id', '=', 'tag_todo.todo_id')
            ->leftJoin('tags', 'tag_todo.tag_id', '=', 'tags.id')
            ->whereNull('todos.deleted_at')
            ->where('todos.is_archived', false)
            ->groupBy('todos.id', 'todos.title', 'todos.notes', 'todos.created_by', 'todos.firebase_todo_id', 'todos.start_time', 'todos.end_time', 'todos.date', 'todos.priority','todos.description')
            ->selectRaw('GROUP_CONCAT(tags.name) as tag_names')
            ->get();

        // Convert `tag_names` to list
        $todos->transform(function ($todo) {
            $todo->tag_names = explode(',', $todo->tag_names);
            return $todo;
        });

        return $todos;
    }

    public function getPerPageTodoList(): array
    {
        $minutesInWeek = 7 * 24 * 60;
        $fields = ['id', 'title', 'description', 'notes', 'firebase_todo_id', 'start_time', 'end_time', 'date', 'priority'];

        $page = request('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;



        $fieldsString = implode(', ', $fields);

        $todosQuery = DB::table('todos')
            ->select(DB::raw($fieldsString))
            ->selectRaw('COUNT(*) OVER() AS total_items')
            ->paginate($this->perPage);

        $paginator = $todosQuery;


        return [
            'current_page' => $paginator->currentPage(),
            'next_page' => $paginator->nextPageUrl(),
            'prev_page' => $paginator->previousPageUrl(),
            'todos' => $paginator->items(),

        ];
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_todo', 'todo_id', 'tag_id');
    }

    public function pomodoros(): HasMany
    {
        return $this->hasMany(Pomodoro::class);
    }
}
