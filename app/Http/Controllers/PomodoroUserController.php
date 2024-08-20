<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePomodoroUserRequest;
use App\Http\Requests\UpdatePomodoroUserRequest;
use App\Models\PomodoroUser;

class PomodoroUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StorePomodoroUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PomodoroUser $pomodoroUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PomodoroUser $pomodoroUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePomodoroUserRequest $request, PomodoroUser $pomodoroUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PomodoroUser $pomodoroUser)
    {
        //
    }
}
