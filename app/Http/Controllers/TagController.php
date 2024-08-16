<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkCreateTagsRequest;
use App\Http\Requests\CreateTagRequest;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

use function App\Helper\errorMsg;
use function App\Helper\successMessage;

class TagController extends Controller
{
    public function getAllTags(Request $request): JsonResponse
    {

        $taskId = (int) $request->input("todo_id");
        $page = (int) $request->input('page', 1);

        if ($taskId <= 0) {
            return errorMsg("Invalid Task ID", 400);
        }

        if ($page < 1) {
            return errorMsg("Invalid pagination page number", 400);
        }

        try {
            $tags = resolve(Tag::class)->getTagsByTaskId($taskId, $page);
            return successMessage(data: $tags);
        } catch (ModelNotFoundException $e) {
            return errorMsg($e->getMessage(), 404);
        } catch (InvalidArgumentException $e) {
            return errorMsg($e->getMessage(), 400);
        } catch (Exception $e) {
            return errorMsg("An unexpected error occurred. Please try again later.", 500);
        }
    }

    public function getAllSeededTags(Request $request): JsonResponse
    {
        $page = (int) $request->input('page', 1);

        if ($page < 1) {
            return errorMsg("Invalid pagination page number", 400);
        }

        try {
            $tags = resolve(Tag::class)->getSeededTags($page);
            return successMessage(data: $tags);
        } catch (ModelNotFoundException $e) {
            return errorMsg($e->getMessage(), 404);
        } catch (InvalidArgumentException $e) {
            return errorMsg($e->getMessage(), 400);
        } catch (Exception $e) {
            return errorMsg("An unexpected error occurred. Please try again later.", 500);
        }
    }



    public function createTag(CreateTagRequest $request): JsonResponse
    {
        $tagModel = new Tag();
        $result = $tagModel->createTag($request);

        if (!$result) {
            return errorMsg("Failed to create tag", 500);
        }

        return successMessage("Tag created successfully", true);
    }



    public function updateTag(Request $request): JsonResponse
    {
        try {
            $result = resolve(Tag::class)->updateTag($request);

            if ($result) {
                return successMessage('Tag updated successfully');
            } else {
                return errorMsg('Failed to update tag', 500);
            }
        } catch (ValidationException $e) {
            return errorMsg($e->getMessage(), 422, $e->errors());
        } catch (Exception $e) {
            return errorMsg('An unexpected error occurred: ' . $e->getMessage(), 500);
        }
    }


    public function deleteTag(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:tags,id',
            ]);

            $result = resolve(Tag::class)->deleteTag($id);

            if ($result) {
                return successMessage('Tag deleted successfully');
            } else {
                return errorMsg('Failed to delete tag', 500);
            }
        } catch (ValidationException $e) {
            return errorMsg($e->getMessage(), 422, $e->errors());
        } catch (Exception $e) {
            return errorMsg('An unexpected error occurred: ' . $e->getMessage(), 500);
        }
    }


    public function bulkCreateTags(BulkCreateTagsRequest $request): JsonResponse
    {
        try {
            $result = (new Tag())->createBulkTags($request);
            return successMessage("Tags created successfully.", true, $result);
        } catch (QueryException $e) {
            return errorMsg('Database error occurred.', 500, ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return errorMsg('An error occurred during tag creation.', 500, ['error' => $e->getMessage()]);
        }
    }

    public function bulkDeleteTags(Request $request): JsonResponse
    {
        try {
            $result = (new Tag())->bulkDeleteTags($request);
            return successMessage('Tags deleted successfully.', true, ['deleted' => $result]);
        } catch (QueryException $e) {
            return errorMsg('A database error occurred during tag deletion.', 500, $e->getMessage());
        } catch (Exception $e) {
            return errorMsg('An error occurred during tag deletion.', 500, $e->getMessage());
        }
    }

    public function archiveTag($id)
    {

    }

    public function restoreTag(Request $request): JsonResponse
    {
        $tagId = $request->input('tag_id');

        try {
            $tag = new Tag();
            $tag->restoreTag($request);

            return successMessage('Tag restored successfully.');
        } catch (QueryException $e) {
            return errorMsg('Query error during tag restoration.', 500, $e->getMessage());
        } catch (Exception $e) {
            return errorMsg('Error during tag restoration.', 500, $e->getMessage());
        }
    }

    public function searchTags(Request $request): JsonResponse
    {
        try {
            $tags = (new Tag())->searchTags($request);
            return successMessage('Tags retrieved successfully.', true, $tags);
        } catch (InvalidArgumentException $e) {
            return errorMsg($e->getMessage(), 400);
        } catch (ModelNotFoundException $e) {
            return errorMsg($e->getMessage(), 404);
        } catch (Exception $e) {
            return errorMsg('An error occurred while fetching tags.', 500, $e->getMessage());
        }
    }
}
