<?php

namespace App\Http\Controllers;

use App\Models\ProjectStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class ProjectStatusController extends Controller
{
    public function retrieveStatusesByUser(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        try {
            $statuses = (new ProjectStatus())->getPaginatedStatusesForUser($userId, $page, $perPage);
            return successMessage("Statuses retrieved successfully.", true, $statuses);
        } catch (\Exception $e) {
            return errorMsg('Failed to retrieve statuses: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createStatusForUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'indicators' => 'nullable|array', // Assuming indicators can be an array
        ]);

        $userId = Auth::id();
        $statusName = $request->input('name');
        $indicators = $request->input('indicators', []);

        $status = new ProjectStatus();

        try {
            if ($status->createStatusIfNotExists($statusName, $userId)) {
                return successMessage('Status created successfully.', true, null);
            }
            return errorMsg('Status already exists.', Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return errorMsg('Failed to create status: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStatusForUser(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:project_statuses,id', // Validate ID
            'name' => 'required|string|max:255',
            'indicators' => 'nullable|array', // Assuming indicators can be an array
        ]);

        $userId = Auth::id();
        $id = $request->input('id'); // Retrieve ID from the request
        $data = $request->only('name', 'indicators');

        $status = new ProjectStatus();

        try {
            if ($status->updateStatus($id, $userId, $data)) {
                return successMessage('Status updated successfully.');
            }
            return errorMsg('Failed to update status or status not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to update status: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteStatusForUser(Request $request): JsonResponse
    {
        $id = $request->route('id'); // Assuming the ID is passed as a route parameter
        $userId = Auth::id();
        $status = new ProjectStatus();

        try {
            if ($status->deleteStatus($id, $userId)) {
                return successMessage('Status deleted successfully.');
            }
            return errorMsg('Failed to delete status or status not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to delete status: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
