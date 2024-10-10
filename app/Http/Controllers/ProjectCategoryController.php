<?php

namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class ProjectCategoryController extends Controller
{
    public function retrieveCategoriesByUser(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        try {
            $categories = (new ProjectCategory())->getPaginatedCategoriesForUser($userId, $page, $perPage);
            return successMessage("Categories retrieved successfully.", true, $categories);
        } catch (\Exception $e) {
            return errorMsg('Failed to retrieve categories: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createCategoryForUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        $userId = Auth::id();
        $categoryName = $request->input('name');
        $metadata = $request->input('metadata');

        $category = new ProjectCategory();

        try {
            if ($category->createCategoryIfNotExists($categoryName, $userId, $metadata)) {
                return successMessage('Category created successfully.', true, null);
            }
            return errorMsg('Category already exists.', Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return errorMsg('Failed to create category: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateCategoryForUser(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:project_categories,id', // Validate ID
            'name' => 'required|string|max:255',
        ]);

        $userId = Auth::id();
        $id = $request->input('id'); // Retrieve ID from the request
        $data = $request->only('name');

        $category = new ProjectCategory();

        try {
            if ($category->updateCategory($id, $userId, $data)) {
                return successMessage('Category updated successfully.');
            }
            return errorMsg('Failed to update category or category not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to update category: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteCategoryForUser(Request $request): JsonResponse
    {
        $id = $request->route('id'); // Assuming the ID is passed as a route parameter
        $userId = Auth::id();
        $category = new ProjectCategory();

        try {
            if ($category->deleteCategory($id, $userId)) {
                return successMessage('Category deleted successfully.');
            }
            return errorMsg('Failed to delete category or category not found.', Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return errorMsg('Failed to delete category: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
