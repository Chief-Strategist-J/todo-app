<?php

namespace App\Http\Controllers;

use App\Models\ProjectPriority;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;
class ProjectPriorityController extends Controller
{
    public function retrievePrioritiesByUser(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        try {
            $priorities = (new ProjectPriority())->getPaginatedPrioritiesForUser($userId, $page, $perPage);
            return successMessage("Priorities retrieved successfully.", true, $priorities);
        } catch (\Exception $e) {
            return errorMsg('Failed to retrieve priorities: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createPriorityForUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'settings' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $priorityName = $request->input('name');
        $settings = $request->input('settings', []);

        $priority = new ProjectPriority();

        try {
            if ($priority->createPriorityIfNotExists($priorityName, $userId)) {
                return successMessage('Priority created successfully.', true, null);
            }
            return errorMsg('Priority already exists.', Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return errorMsg('Failed to create priority: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatePriorityForUser(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:project_priorities,id', // Validate ID
            'name' => 'required|string|max:255',
            'settings' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $id = $request->input('id'); // Retrieve ID from the request
        $data = $request->only('name', 'settings');

        $priority = new ProjectPriority();

        try {
            if ($priority->updatePriority($id, $userId, $data)) {
                return successMessage('Priority updated successfully.');
            }
            return errorMsg('Failed to update priority or priority not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to update priority: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deletePriorityForUser(Request $request): JsonResponse
    {
        $id = $request->route('id'); // Assuming the ID is passed as a route parameter
        $userId = Auth::id();
        $priority = new ProjectPriority();

        try {
            if ($priority->deletePriority($id, $userId)) {
                return successMessage('Priority deleted successfully.');
            }
            return errorMsg('Failed to delete priority or priority not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to delete priority: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
