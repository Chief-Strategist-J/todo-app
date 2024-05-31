<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;
use function Laravel\Prompts\error;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $minutesInWeek = 7 * 24 * 60;
        $queryResult = Cache::remember('todoList', $minutesInWeek, function () {
            return DB::table('todos')
                ->select('id', 'title', 'description', 'notes')
                ->get();
        });

        return $queryResult;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTodoRequest $request): JsonResponse
    {


        try {

            $todo = new Todo();
            $todo->title = $request->input("title");
            $todo->description = $request->input("description");
            $todo->notes = $request->input("notes");


            if ($request->has('due_date')) {
                $todo->due_date = $request->input('due_date');
            }

            if ($request->has('priority')) {
                $todo->priority = $request->input('priority');
            }

            if ($request->has('assigned_to')) {
                $todo->assigned_to = $request->input('assigned_to');
            }

            if ($request->has('tags')) {
                $todo->tags = $request->input('tags');
            }

            if ($request->has('created_by')) {
                $todo->created_by = $request->input('created_by');
            }

            if ($request->has('updated_by')) {
                $todo->updated_by = $request->input('updated_by');
            }

            if ($request->has('status')) {
                $todo->status = $request->input('status');
            }

            if ($request->has('reminder')) {
                $todo->reminder = $request->input('reminder');
            }

            if ($request->has('updated_by')) {
                $todo->updated_by = $request->input('updated_by');
            }

            if ($request->has('attachment')) {
                $todo->attachment = $request->input('attachment');
            }

            if ($request->has('category')) {
                $todo->category = $request->input('category');
            }

            if ($request->has('estimated_time')) {
                $todo->estimated_time = $request->input('estimated_time');
            }

            if ($request->has('actual_time')) {
                $todo->actual_time = $request->input('actual_time');
            }

            if ($request->has('location')) {
                $todo->location = $request->input('location');
            }

            if ($request->has('recurring')) {
                $todo->recurring = $request->input('recurring');
            }

            if ($request->has('recurring_frequency')) {
                $todo->recurring_frequency = $request->input('recurring_frequency');
            }
            if ($request->has('completed_at')) {
                $todo->completed_at = $request->input('completed_at');
            }
            if ($request->has('color_code')) {
                $todo->color_code = $request->input('color_code');
            }

            if ($request->has('is_archived')) {
                $todo->is_archived = $request->input('is_archived');
            }

            Cache::forget('todoList');
            $todo->save();

            return successMessage(
                data: [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'notes' => $todo->notes
                ]
            );
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e->getMessage());
        }
    }

    public function updateTodo(UpdateTodoRequest $request)
    {


        try {
            $todo = Todo::find($request->input("todo_id"));

            if (is_null($todo)) {
                return successMessage(data: ['message' => "todo id is not exists"]);
            }

            if ($request->has('title')) {
                $todo->title = $request->input('title');
            }

            if ($request->has('description')) {
                $todo->description = $request->input('description');
            }

            if ($request->has('notes')) {
                $todo->notes = $request->input('notes');
            }

            if ($request->has('due_date')) {
                $todo->due_date = $request->input('due_date');
            }

            if ($request->has('priority')) {
                $todo->priority = $request->input('priority');
            }

            if ($request->has('assigned_to')) {
                $todo->assigned_to = $request->input('assigned_to');
            }

            if ($request->has('tags')) {
                $todo->tags = $request->input('tags');
            }

            if ($request->has('created_by')) {
                $todo->created_by = $request->input('created_by');
            }

            if ($request->has('updated_by')) {
                $todo->updated_by = $request->input('updated_by');
            }

            if ($request->has('status')) {
                $todo->status = $request->input('status');
            }

            if ($request->has('reminder')) {
                $todo->reminder = $request->input('reminder');
            }

            if ($request->has('updated_by')) {
                $todo->updated_by = $request->input('updated_by');
            }

            if ($request->has('attachment')) {
                $todo->attachment = $request->input('attachment');
            }

            if ($request->has('category')) {
                $todo->category = $request->input('category');
            }

            if ($request->has('estimated_time')) {
                $todo->estimated_time = $request->input('estimated_time');
            }

            if ($request->has('actual_time')) {
                $todo->actual_time = $request->input('actual_time');
            }

            if ($request->has('location')) {
                $todo->location = $request->input('location');
            }

            if ($request->has('recurring')) {
                $todo->recurring = $request->input('recurring');
            }

            if ($request->has('recurring_frequency')) {
                $todo->recurring_frequency = $request->input('recurring_frequency');
            }
            if ($request->has('completed_at')) {
                $todo->completed_at = $request->input('completed_at');
            }
            if ($request->has('color_code')) {
                $todo->color_code = $request->input('color_code');
            }

            if ($request->has('is_archived')) {
                $todo->is_archived = $request->input('is_archived');
            }

            Cache::forget('todoList');
            $todo->update();

            return successMessage(data: $todo);
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Todo $todo)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        $this->updateTodo($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        try {
            $todo = Todo::find($request->input("todo_id"));
            $todo->delete();

            return successMessage(data: 'TODO ID: '.$request->input("todo_id") . ' is deleted from record');
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e->getTrace());
        }
    }
}
