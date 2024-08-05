<?php

namespace App\Jobs;

use App\Models\Todo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use function App\Helper\sendNotification;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public function __construct(
        public $id,
        public string $title,
        public string $message,
        public string $email,
        public ?string $scheduledTime = null
    ) {
    }

    public function handle(): void
    {
        $todo = Todo::find($this->id);

        if (!$todo) {
            Log::warning("Todo not found");
            return;
        }

        sendNotification(
            title: $this->title,
            message: $this->message,
            emails: $this->email,
            scheduledTime: $this->scheduledTime
        );
    }
}
