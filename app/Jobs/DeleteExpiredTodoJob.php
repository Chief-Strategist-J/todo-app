<?php

namespace App\Jobs;

use App\Models\Todo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;


class DeleteExpiredTodoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $todoId)
    {
    }

    public function handle(): void
    {
        Log::info("Starting deletion process for todo ID: {$this->todoId}");

        try {
            $todo = Todo::find($this->todoId);

            if (!$todo) {
                Log::warning("Todo with ID {$this->todoId} not found");
                return;
            }

            $factory = (new Factory)->withServiceAccount(env('FIREBASE_CREDENTIALS'));
            $firestore = $factory->createFirestore();
            $database = $firestore->database();
            $collection = $database->collection('todo');

            $document = $collection->document($todo->firebase_todo_id);

            if (!$document->snapshot()->exists()) {
                Log::warning("Firestore document for todo ID {$this->todoId} not found");
            } else {
                $document->delete();
                Log::info("Firestore document deleted for todo ID: {$this->todoId}");
            }

            $todo->delete();
            Log::info("Todo with ID {$this->todoId} deleted from database");
        } catch (\Exception $e) {
            Log::error("Error while deleting todo ID {$this->todoId}: " . $e->getMessage());
            throw $e;  // Re-throw to mark job as failed
        }
    }
}
