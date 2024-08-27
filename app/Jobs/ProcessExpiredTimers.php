<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessExpiredTimers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $timerId;

    public function __construct(int $timerId)
    {
        $this->timerId = $timerId;
    }

    public function handle()
    {
        $now = now();

        $affectedRows = DB::table('pomodoro_timers')
            ->where('id', $this->timerId)
            ->whereNull('completed_at')
            ->update([
                'completed_at' => $now,
                'status' => 'completed',
                'updated_at' => $now,
            ]);

        if ($affectedRows > 0) {
            Log::info("Timer {$this->timerId} marked as completed at {$now}");
        } else {
            Log::warning("Timer {$this->timerId} not found or already completed.");
        }
    }
}
