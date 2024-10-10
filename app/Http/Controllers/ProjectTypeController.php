<?php

namespace App\Http\Controllers;

use App\Models\ProjectType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class ProjectTypeController extends Controller
{
    public function retrieveTypesByUser(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        try {
            $types = (new ProjectType())->getPaginatedTypesForUser($userId, $page, $perPage);
            return successMessage("Project types retrieved successfully.", true, $types);
        } catch (\Exception $e) {
            return errorMsg('Failed to retrieve project types: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createTypeForUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $typeName = $request->input('name');

        $type = new ProjectType();

        try {
            if ($type->createTypeIfNotExists($typeName, $userId)) {
                return successMessage('Project type created successfully.', true, null);
            }
            return errorMsg('Project type already exists.', Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return errorMsg('Failed to create project type: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateTypeForUser(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:project_types,id', // Validate ID
            'name' => 'required|string|max:255',
            'attributes' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $id = $request->input('id'); // Retrieve ID from the request
        $data = $request->only('name', 'attributes');

        $type = new ProjectType();

        try {
            if ($type->updateType($id, $userId, $data)) {
                return successMessage('Project type updated successfully.');
            }
            return errorMsg('Failed to update project type or project type not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to update project type: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteTypeForUser(Request $request): JsonResponse
    {
        $id = $request->route('id'); // Assuming the ID is passed as a route parameter
        $userId = Auth::id();
        $type = new ProjectType();

        try {
            if ($type->deleteType($id, $userId)) {
                return successMessage('Project type deleted successfully.');
            }
            return errorMsg('Failed to delete project type or project type not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to delete project type: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
