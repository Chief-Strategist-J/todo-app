<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePomodoroProjectRequest;
use App\Http\Requests\UpdatePomodoroProjectRequest;
use App\Models\PomodoroProject;

class PomodoroProjectController extends Controller
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
    public function store(StorePomodoroProjectRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PomodoroProject $pomodoroProject)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PomodoroProject $pomodoroProject)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePomodoroProjectRequest $request, PomodoroProject $pomodoroProject)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PomodoroProject $pomodoroProject)
    {
        //
    }
}
