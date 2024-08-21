<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Jobs\SendNotificationJob;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;
use function App\Helper\errorMsg;
use function App\Helper\getIndianTime;
use function App\Helper\successMessage;

class TodoController extends Controller
{
    public function index(): JsonResponse
    {
        return successMessage(data: resolve(Todo::class)->getTodoList());
    }

    public function getlistOfTodosPagignated(): JsonResponse
    {
        return successMessage(data: resolve(Todo::class)->getPerPageTodoList());
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        try {
            $createdBy = $request->input('created_by');

            $cacheKey = 'user_' . $createdBy;

            $user = Cache::remember($cacheKey, now()->addWeek(), function () use ($createdBy) {
                return User::find($createdBy);
            });

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $email = $user->email;

            $title = $request->input('title');
            $message = $request->input('description');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            $startTimeFormatted = date('h:i A', strtotime($startTime));
            $endTimeFormatted = date('h:i A', strtotime($endTime));

            $startMessage = "A gentle reminder: your task '$title' is scheduled to start at $startTimeFormatted.";
            $endMessage = "A gentle reminder: your task '$title' is scheduled to end at $endTimeFormatted.";

            $todo = resolve(Todo::class)->createTodo(request: $request);

            SendNotificationJob::dispatch($todo->id, $title, $startMessage, $email, $startTime)->delay(getIndianTime($request->input('start_time')));
            SendNotificationJob::dispatch($todo->id, $title, $message, $email);
            SendNotificationJob::dispatch($todo->id, $title, $endMessage, $email, $endTime)->delay(getIndianTime($request->input('end_time')));

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

    public function testing(Request $request)
    {
        return response()->json([
            'message' => 'done',
        ]);
    }



    public function updateTodo(UpdateTodoRequest $request): JsonResponse
    {
        try {
            return resolve(Todo::class)->updateTodo(request: $request);
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e);
        }
    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        return resolve(Todo::class)->updateTodo($request);
    }

    public function deleteTodo(Request $request): JsonResponse
    {
        try {
            $todo = Todo::find($request->input("todo_id"));
            Log::info("print".$todo);

            if ($todo) {
                $todo->forceDelete();
                return successMessage(data: 'TODO ID: ' . $request->input("todo_id") . ' is deleted from record');
            } else {
                return successMessage(data: 'TODO ID: ' . $request->input("todo_id") . ' is not in record   ');
            }
        } catch (Throwable $e) {
            report($e);
            Log::error($e);
            return errorMsg(message: $e->getTrace());
        }
    }

    public function create()
    {
    }

    public function show(Todo $todo)
    {
    }

    public function edit(Todo $todo)
    {
    }
}
