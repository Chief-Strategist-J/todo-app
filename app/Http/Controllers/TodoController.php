<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Throwable;
use function App\Helper\errorMsg;
use function App\Helper\sendNotification;
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

            sendNotification(title: $title, message: $startMessage, emails: $email, scheduledTime: $startTime);
            sendNotification(title: $title, message: $message, emails: $email);
            sendNotification(title: $title, message: $endMessage, emails: $email, scheduledTime: $endTime);

            return resolve(Todo::class)->createTodo(request: $request);
        } catch (Throwable $e) {
            report($e);
            return errorMsg(message: $e->getMessage());
            
        }
    }

    public function testing()
    {
        $factory = (new Factory)->withServiceAccount(env('FIREBASE_CREDENTIALS'));
        $firestore = $factory->createFirestore();
        $database = $firestore->database();

        // Example: Read data from a collection
        $collection = $database->collection('todo');
        $documents = $collection->documents();

        $data = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $data[] = $document->data();
            }
        }

        return response()->json($data);

    }


    public function updateTodo(UpdateTodoRequest $request): JsonResponse
    {
        try {

            // Check if user exists
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
