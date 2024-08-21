<?php

namespace Tests\Feature;

use App\Models\Pomodoro;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Str;
class PomodoroModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_bulk_pomodoros()
    {
        $todo = Todo::factory()->create();
        $user = User::factory()->create();

        $title = 'Test Pomodoro';
        $duration = 25;
        $status = 'pending';
        $todoId = $todo->id;
        $userId = $user->id;
        $numberOfPomodoros = 5;

        $request = new Request([
            'title' => $title,
            'duration' => $duration,
            'status' => $status,
            'todo_id' => $todoId,
            'user_id' => $userId,
            'number_of_pomodoros' => $numberOfPomodoros,
        ]);

        $pomodoroModel = new Pomodoro();
        $response = $pomodoroModel->create_bulk_pomodoros($request);

        $this->assertEquals(201, $response->status());

        $responseData = $response->getData(true);

        $this->assertCount($numberOfPomodoros, $responseData);

        foreach ($responseData as $pomodoro) {
            $this->assertArrayHasKey('id', $pomodoro);
            $this->assertArrayHasKey('uuid', $pomodoro);
            $this->assertArrayHasKey('title', $pomodoro);
            $this->assertArrayHasKey('duration', $pomodoro);
            $this->assertArrayHasKey('status', $pomodoro);
            $this->assertArrayHasKey('todo_id', $pomodoro);
            $this->assertArrayHasKey('user_id', $pomodoro);
            $this->assertArrayHasKey('created_at', $pomodoro);
            $this->assertArrayHasKey('updated_at', $pomodoro);
        }
    }

    public function test_start_pomodoro_creates_timer_when_none_exists()
    {

        $todo = Todo::factory()->create();
        $user = User::factory()->create();
        $pomodoro = Pomodoro::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);


        $pomodoroModel = new Pomodoro();
        $pomodoroModel->startPomodoro($pomodoro->id);


        $this->assertDatabaseHas('pomodoro_timers', [
            'pomodoro_id' => $pomodoro->id,
            'status' => 'started',
        ]);

        $this->assertDatabaseHas('pomodoros', [
            'id' => $pomodoro->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseCount('pomodoro_timers', 1); // Ensure only one timer is created
    }


    public function test_stop_pomodoro_updates_timer_and_pomodoro()
    {
        // Arrange
        $todo = Todo::factory()->create();
        $user = User::factory()->create();
        $pomodoro = Pomodoro::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        // Create an active timer
        $startTime = now()->subMinutes(10); // 10 minutes ago
        DB::table('pomodoro_timers')->insert([
            'pomodoro_id' => $pomodoro->id,
            'started_at' => $startTime,
            'status' => 'started',
            'segment_duration_seconds' => 0,
            'pause_duration_seconds' => 0,
            'active_duration_seconds' => 0,
            'is_interrupted' => false,
            'number_of_pauses' => 0,
            'device_used' => null,
            'user_feedback' => null,
            'performance_score' => null,
        ]);

        // Act
        $pomodoroModel = new Pomodoro();
        $pomodoroModel->stopPomodoro($pomodoro->id);

        // Assert
        $timer = DB::table('pomodoro_timers')->where('pomodoro_id', $pomodoro->id)->first();
        $this->assertNotNull($timer);
        $this->assertNotNull($timer->stopped_at);
        $this->assertEquals('stopped', $timer->status);

        $this->assertDatabaseHas('pomodoros', [
            'id' => $pomodoro->id,
            'status' => 'paused',
            'end_time' => $timer->stopped_at,
        ]);
    }

    public function test_resume_pomodoro_updates_timer_and_pomodoro()
    {
        // Arrange
        $todo = Todo::factory()->create();
        $user = User::factory()->create();
        $pomodoro = Pomodoro::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        // Insert a timer that is eligible for resuming
        $startTime = now()->subMinutes(10); // 10 minutes ago
        DB::table('pomodoro_timers')->insert([
            'pomodoro_id' => $pomodoro->id,
            'started_at' => $startTime,
            'status' => 'stopped',
            'segment_duration_seconds' => 600, // 10 minutes
            'pause_duration_seconds' => 0,
            'active_duration_seconds' => 600,
            'is_interrupted' => false,
            'number_of_pauses' => 0,
            'device_used' => null,
            'user_feedback' => null,
            'performance_score' => null,
        ]);

        // Act
        $pomodoroModel = new Pomodoro();
        $pomodoroModel->resumePomodoro($pomodoro->id);

        // Assert
        $timer = DB::table('pomodoro_timers')->where('pomodoro_id', $pomodoro->id)->first();
        $this->assertNotNull($timer);
        $this->assertEquals('stopped', $timer->status);
    }

    public function test_end_pomodoro_updates_timer_and_pomodoro()
    {
        // Arrange
        $todo = Todo::factory()->create();
        $user = User::factory()->create();
        $pomodoro = Pomodoro::factory()->create([
            'todo_id' => $todo->id,
            'user_id' => $user->id,
        ]);

        // Insert a timer with the status 'started'
        $startTime = now()->subMinutes(10); // 10 minutes ago
        DB::table('pomodoro_timers')->insert([
            'pomodoro_id' => $pomodoro->id,
            'started_at' => $startTime,
            'status' => 'started',
            'segment_duration_seconds' => 0,
            'duration_in_seconds' => 0,
        ]);

        // Act
        $pomodoroModel = new Pomodoro();
        $pomodoroModel->endPomodoro($pomodoro->id);

        // Assert
        $timer = DB::table('pomodoro_timers')->where('pomodoro_id', $pomodoro->id)->first();
        $this->assertNotNull($timer);
        $this->assertNotNull($timer->completed_at);
        $this->assertEquals('completed', $timer->status);

        

        $this->assertDatabaseHas('pomodoros', [
            'id' => $pomodoro->id,
            'status' => 'completed',
            'end_time' => $timer->completed_at,
        ]);
    }
}
