<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

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


        $todo = new Todo();

        $todo->title = $request->input("title");
        $todo->description = $request->input("description");
        $todo->notes = $request->input("notes");

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
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Todo $todo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTodoRequest $request, Todo $todo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        //
    }
}
