<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBulkPomodorosRequest;
use App\Http\Requests\GetPomodoroStatsRequest;
use App\Http\Requests\StartPomodoroRequest;
use App\Models\Pomodoro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class PomodoroController extends Controller
{
    protected Pomodoro $pomodoro;

    public function __construct(Pomodoro $pomodoro)
    {
        $this->pomodoro = $pomodoro;
    }

    public function createBulkPomodoros(CreateBulkPomodorosRequest $request): JsonResponse
    {
        try {
            $result = $this->pomodoro->createBulkPomodoros($request);
            return successMessage('Pomodoros created successfully', true, $result->getData());
        } catch (Exception $e) {
            Log::error('Failed to create bulk pomodoros: ' . $e->getMessage());
            return errorMsg('Failed to create pomodoros', false);
        }
    }

    public function startPomodoro(StartPomodoroRequest $request): void
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->startPomodoro($pomodoroId);
            Log::error('Started the pomodoro');
        } catch (Exception $e) {
            Log::error('Failed to start pomodoro: ' . $e->getMessage());
        }
    }

    public function stopPomodoro(StartPomodoroRequest $request): void
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->stopPomodoro($pomodoroId);
            Log::error('Stoped the pomodoro');
        } catch (Exception $e) {
            Log::error('Failed to stop pomodoro: ' . $e->getMessage());
        }
    }

    public function resumePomodoro(StartPomodoroRequest $request): void
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->resumePomodoro($pomodoroId);
            Log::error('Resumed the pomodoro');
        } catch (Exception $e) {
            Log::error('Failed to resume pomodoro: ' . $e->getMessage());
        }
    }

    public function endPomodoro(StartPomodoroRequest $request): void
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->endPomodoro($pomodoroId);
            Log::error('Ended the pomodoro');
        } catch (Exception $e) {
            Log::error('Failed to end pomodoro: ' . $e->getMessage());
        }
    }

    public function getPomodoroStats(GetPomodoroStatsRequest $request): JsonResponse
    {
        try {
            $userId = (int) $request->input('user_id');
            $result = $this->pomodoro->getPomodoroStats($userId);
            return successMessage('Pomodoro stats retrieved successfully', true, $result->getData());
        } catch (Exception $e) {
            Log::error('Failed to retrieve pomodoro stats: ' . $e->getMessage());
            return errorMsg('Failed to retrieve pomodoro stats', false);
        }
    }
}
