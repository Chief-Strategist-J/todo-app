<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class TodoController extends Controller
{
    public function index(): JsonResponse
    {
        $todo = new Todo();
        return successMessage(data: $todo->getTodoList());
    }

    public function create()
    {
        //
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        try {
            $todo = new Todo();
            return $todo->createTodo(request: $request);
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e->getMessage());
        }
    }

    public function updateTodo(UpdateTodoRequest $request): JsonResponse
    {
        try {
            $todo = Todo::find($request->input("todo_id"));
            return $todo->updateTodo(request: $request);
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e);
        }
    }

    public function show(Todo $todo)
    {

    }

    public function edit(Todo $todo)
    {

    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        return $this->updateTodo($request);
    }

    public function destroy(Request $request): JsonResponse
    {

        try {
            $todo = Todo::find($request->input("todo_id"));
            $todo->delete();

            return successMessage(data: 'TODO ID: ' . $request->input("todo_id") . ' is deleted from record');
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e->getTrace());
        }
    }
}
