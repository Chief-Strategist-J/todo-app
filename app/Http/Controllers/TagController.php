<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkCreateTagsRequest;
use App\Http\Requests\CreateTagRequest;
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

    public function getAllTagsByUserId(Request $request): JsonResponse
    {
        $userId = (int) $request->input('userId');
        $limit = (int) $request->input('limit', 50);  // Default limit to 50 if not provided
        $page = (int) $request->input('page', 1);     // Default page to 1 if not provided

        if ($userId <= 0) {
            return errorMsg("Invalid User ID", 400);
        }

        if ($page < 1) {
            return errorMsg("Invalid pagination page number", 400);
        }

        if ($limit <= 0 || $limit > 50) {
            return errorMsg("Limit must be a positive number and less than or equal to 50.", 400);
        }

        try {
            $tags = resolve(Tag::class)->getUserTags($userId, $limit, $page);
            return successMessage(data: $tags);
        } catch (ModelNotFoundException $e) {
            return errorMsg($e->getMessage(), 404);
        } catch (InvalidArgumentException $e) {
            return errorMsg($e->getMessage(), 400);
        } catch (QueryException $e) {
            return errorMsg("A database error occurred. Please try again later.", 500);
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

    public function getTagByTagId(Request $request): JsonResponse
    {
        $tagId = $request->input('id');

        if (!$tagId) {
            return errorMsg('Tag ID is required.', 400);
        }

        $tag = (new Tag())->getTagById($tagId);

        if (!$tag) {
            return errorMsg('Tag not found.', 404);
        }

        return successMessage('Tag retrieved successfully.', true, $tag->toArray());
    }

    public function bulkDeleteTagsByTodoId(Request $request): JsonResponse
    {
        $todoId = $request->input('todo_id');

        if (!$todoId) {
            return errorMsg('Todo ID is required.', 400);
        }

        try {
            $tagModel = new Tag();
            $success = $tagModel->bulkDeleteTagsByTodoId($request);

            if ($success) {
                return successMessage('Tags unlinked from Todo successfully.', true);
            } else {
                return errorMsg('No tags were unlinked from the Todo.', 404);
            }
        } catch (Exception $e) {
            return errorMsg('An error occurred while unlinking tags from the Todo.', 500);
        }
    }

}
