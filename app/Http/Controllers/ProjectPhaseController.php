<?php

namespace App\Http\Controllers;

use App\Models\ProjectPhase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class ProjectPhaseController extends Controller
{
    public function retrievePhasesByUser(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        try {
            $phases = (new ProjectPhase())->getPaginatedPhasesForUser($userId, $page, $perPage);
            return successMessage("Phases retrieved successfully.", true, $phases);
        } catch (\Exception $e) {
            return errorMsg('Failed to retrieve phases: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createPhaseForUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'details' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $phaseName = $request->input('name');
        $details = $request->input('details');

        $phase = new ProjectPhase();

        try {
            if ($phase->createPhaseIfNotExists($phaseName, $userId)) {
                return successMessage('Phase created successfully.', true, null);
            }
            return errorMsg('Phase already exists.', Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return errorMsg('Failed to create phase: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updatePhaseForUser(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:project_phases,id', // Validate ID
            'name' => 'required|string|max:255',
            'details' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $id = $request->input('id'); // Retrieve ID from the request
        $data = $request->only('name', 'details');

        $phase = new ProjectPhase();

        try {
            if ($phase->updatePhase($id, $userId, $data)) {
                return successMessage('Phase updated successfully.');
            }
            return errorMsg('Failed to update phase or phase not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to update phase: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deletePhaseForUser(Request $request): JsonResponse
    {
        $id = $request->route('id'); // Assuming the ID is passed as a route parameter
        $userId = Auth::id();
        $phase = new ProjectPhase();

        try {
            if ($phase->deletePhase($id, $userId)) {
                return successMessage('Phase deleted successfully.');
            }
            return errorMsg('Failed to delete phase or phase not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to delete phase: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
