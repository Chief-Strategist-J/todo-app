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

    public function startPomodoro(StartPomodoroRequest $request): JsonResponse
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->startPomodoro($pomodoroId);
            return successMessage('Pomodoro started successfully');
        } catch (Exception $e) {
            Log::error('Failed to start pomodoro: ' . $e->getMessage());
            return errorMsg('Failed to start pomodoro', false);
        }
    }

    public function stopPomodoro(StartPomodoroRequest $request): JsonResponse
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->stopPomodoro($pomodoroId);
            return successMessage('Pomodoro stopped successfully');
        } catch (Exception $e) {
            Log::error('Failed to stop pomodoro: ' . $e->getMessage());
            return errorMsg('Failed to stop pomodoro', false);
        }
    }

    public function resumePomodoro(StartPomodoroRequest $request): JsonResponse
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->resumePomodoro($pomodoroId);
            return successMessage('Pomodoro resumed successfully');
        } catch (Exception $e) {
            Log::error('Failed to resume pomodoro: ' . $e->getMessage());
            return errorMsg('Failed to resume pomodoro', false);
        }
    }

    public function endPomodoro(StartPomodoroRequest $request): JsonResponse
    {
        try {
            $pomodoroId = (int) $request->input('pomodoro_id');
            $this->pomodoro->endPomodoro($pomodoroId);
            return successMessage('Pomodoro ended successfully');
        } catch (Exception $e) {
            Log::error('Failed to end pomodoro: ' . $e->getMessage());
            return errorMsg('Failed to end pomodoro', false);
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
