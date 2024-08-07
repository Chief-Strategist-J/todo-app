<?php

use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    protected User $user; // Define the $user property

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for the foreign key constraint
        $this->user = User::factory()->create();
    }
    public function testGetTagsByTaskId()
    {
        // Arrange: Set up the necessary data
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Initially, no tags should be related to the task
        $result = (new Tag())->getTagsByTaskId($task->id);
        $this->assertCount(0, $result);

        // Create some tags associated with the task
        $taskTags = Tag::factory()->count(3)->create([
            'todo_id' => $task->id, // Ensure todo_id is set
            'created_by' => $user->id,
        ]);

        // Act: Call the method again after adding the tags
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Check if the result matches the expected tags
        $this->assertCount(3, $result);
        $this->assertEquals($taskTags->pluck('id')->sort()->toArray(), collect($result)->pluck('id')->sort()->toArray());
    }

    public function testGetTagsByTaskId_CachedResults()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(2)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id); // Call the method to cache results

        // Act: Call the method again
        $result = $tagService->getTagsByTaskId($task->id);

        // Assert: Should return cached tags
        $this->assertCount(2, $result);
    }

    public function testGetTagsByTaskId_UpdatedTags()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(2)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id);

        // Act: Add a new tag
        Tag::factory()->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Call the method again
        $result = $tagService->getTagsByTaskId($task->id);

        // Assert: Should return the updated tags count
        $this->assertCount(3, $result); // Expecting 3 now
    }

    public function testGetTagsByTaskId_InvalidTaskId()
    {
        $this->expectException(InvalidArgumentException::class);
        (new Tag())->getTagsByTaskId('invalid'); // Pass an invalid ID
    }

    public function testGetTagsByTaskId_CacheExpiration()
    {
        // Manually set the cache to expire immediately for testing
        Cache::put('tags_by_todo_id_1_page_1', collect([]), now()->subMinute());

        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Act: Call the method after cache expiration
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should return the tags from the database since cache is expired
        $this->assertCount(3, $result);
    }

    public function testGetTagsByTaskId_MultiplePages()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(55)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Act: Call the method to retrieve the first page
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should return the first 50 tags (default pagination)
        $this->assertCount(50, $result);
    }

    public function testGetTagsByTaskId_EmptyCacheWithTagsPresent()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Manually clear the cache to simulate an empty cache
        Cache::forget("tags_by_todo_id_{$task->id}_page_1");

        // Act: Call the method
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should return the created tags
        $this->assertCount(3, $result);
    }

    public function testGetTagsByTaskId_CacheHitWithDifferentPage()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(100)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Act: Call the method to cache results for page 1
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id); // Cache first page results

        // Act: Call the method for page 2
        $result = $tagService->getTagsByTaskId($task->id); // Access different page

        // Assert: Should return the correct tags for page 2 without querying again
        $this->assertCount(50, $result); // Adjust count based on your pagination logic
    }

    public function testGetTagsByTaskId_TagsCreationWithoutPagination()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Create new tags without going through the cache
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Call the method to retrieve tags
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should return the newly created tags
        $this->assertCount(3, $result);
    }

    public function testGetTagsByTaskId_CacheKeyGeneration()
    {
        $user = User::factory()->create();
        $task1 = Todo::factory()->create(['created_by' => $user->id]);
        $task2 = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Create tags for both tasks
        Tag::factory()->count(2)->create(['todo_id' => $task1->id, 'created_by' => $user->id]);
        Tag::factory()->count(2)->create(['todo_id' => $task2->id, 'created_by' => $user->id]);

        // Assert: Ensure cache keys are unique for different tasks
        $cacheKey1 = "tags_by_todo_id_{$task1->id}_page_1";
        $cacheKey2 = "tags_by_todo_id_{$task2->id}_page_1";

        // Verify that cache keys are different
        $this->assertNotEquals($cacheKey1, $cacheKey2);
    }

    public function testGetTagsByTaskId_NoTagsPresent()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Call the method for a task without any tags
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should return an empty collection
        $this->assertCount(0, $result);
    }


    public function testGetTagsByTaskId_PaginatedResults()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(100)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Act: Call the method to retrieve the second page
        $result = (new Tag())->getTagsByTaskId($task->id, 2); // Assuming a second parameter for page number

        // Assert: Should return the correct number of tags for page 2
        $this->assertCount(50, $result); // Adjust based on your pagination logic
    }

    public function testGetTagsByTaskId_HandleNoTagsEdgeCase()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Call the method with a task that has no tags
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should handle gracefully, returning an empty array
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function testGetTagsByTaskId_CacheBehaviorOnMultipleCalls()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id);

        // Act: Call the method multiple times to check cache behavior
        $result1 = $tagService->getTagsByTaskId($task->id);
        $result2 = $tagService->getTagsByTaskId($task->id);

        // Assert: Both results should be the same
        $this->assertEquals($result1, $result2);
    }

    public function testGetTagsByTaskId_CacheFlushAfterTagUpdate()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        $tag = Tag::factory()->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id);

        // Act: Update the tag
        $tag->name = 'Updated Tag';
        $tag->save();

        // Call the method again to check if the cache is updated
        $result = $tagService->getTagsByTaskId($task->id);

        // Assert: Should still return the updated tag
        $this->assertCount(1, $result);
        $this->assertEquals('Updated Tag', $result[0]->name);
    }

    public function testGetTagsByTaskId_NonExistentTaskId()
    {
        $this->expectException(ModelNotFoundException::class);
        (new Tag())->getTagsByTaskId(9999); // Pass an ID that does not exist
    }

    public function testGetTagsByTaskId_PaginatedWithEmptyResults()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Call the method for a task that has no tags
        $result = (new Tag())->getTagsByTaskId($task->id); // Assuming a default of page 1

        // Assert: Should return an empty collection
        $this->assertCount(0, $result);
    }

    public function testGetTagsByTaskId_CacheBehaviorAfterMultipleUpdates()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id);

        // Act: Update the tags multiple times
        foreach (Tag::where('todo_id', $task->id)->get() as $tag) {
            $tag->name = 'Updated ' . $tag->name;
            $tag->save();
        }

        // Call the method again to check if the cache is updated
        $result = $tagService->getTagsByTaskId($task->id);

        // Assert: Should return the updated tags
        $this->assertCount(3, $result);
        foreach ($result as $tag) {
            $this->assertStringStartsWith('Updated ', $tag->name);
        }
    }

    public function testGetTagsByTaskId_CacheBehaviorAfterMultipleCreations()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Create multiple tags
        Tag::factory()->count(5)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id);

        // Act: Create more tags
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Call the method again to check if the cache is updated
        $result = $tagService->getTagsByTaskId($task->id);

        // Assert: Should return the total count of tags
        $this->assertCount(8, $result);
    }

    public function testGetTagsByTaskId_DeletingTagsInBulk()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        $tags = Tag::factory()->count(5)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Act: Delete all tags in bulk (soft delete)
        Tag::where('todo_id', $task->id)->delete(); // Soft delete

        // Call the method again to check if the tags are gone
        $result = (new Tag())->getTagsByTaskId($task->id);

        // Assert: Should return an empty collection since all tags were deleted
        $this->assertCount(0, $result);
    }


    public function testGetTagsByTaskId_InvalidPaginationInput()
    {
        $this->expectException(InvalidArgumentException::class);
        (new Tag())->getTagsByTaskId(1, -1); // Pass an invalid pagination page
    }


    public function testGetTagsByTaskId_TagCountMismatch()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);
        Tag::factory()->count(3)->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Pre-cache the results
        $tagService = new Tag();
        $tagService->getTagsByTaskId($task->id);

        // Act: Create an additional tag that causes a count mismatch
        Tag::factory()->create(['todo_id' => $task->id, 'created_by' => $user->id]);

        // Call the method again
        $result = $tagService->getTagsByTaskId($task->id);

        // Assert: Should return 4 tags since the cache would invalidate
        $this->assertCount(4, $result);
    }

    public function testGetTagsByTaskId_MultipleTasksWithSameUser()
    {
        $user = User::factory()->create();
        $task1 = Todo::factory()->create(['created_by' => $user->id]);
        $task2 = Todo::factory()->create(['created_by' => $user->id]);

        // Create tags for both tasks
        Tag::factory()->count(2)->create(['todo_id' => $task1->id, 'created_by' => $user->id]);
        Tag::factory()->count(3)->create(['todo_id' => $task2->id, 'created_by' => $user->id]);

        // Act: Retrieve tags for task1
        $result1 = (new Tag())->getTagsByTaskId($task1->id);
        $this->assertCount(2, $result1);

        // Act: Retrieve tags for task2
        $result2 = (new Tag())->getTagsByTaskId($task2->id);
        $this->assertCount(3, $result2);
    }

    public function testGetTagsByTaskId_EmptyTagsWithPagination()
    {
        $user = User::factory()->create();
        $task = Todo::factory()->create(['created_by' => $user->id]);

        // Act: Call the method with pagination for a task with no tags
        $result = (new Tag())->getTagsByTaskId($task->id, 1); // Page 1

        // Assert: Should return an empty collection
        $this->assertCount(0, $result);
    }

    public function testItFetchesPopularTagsWithDefaultPagination()
    {
        Tag::factory()->count(100)->create();

        $tags = (new Tag)->getPopularTags();

        $this->assertNotEmpty($tags);
        $this->assertCount(50, $tags);
    }

    public function testItCachesThePopularTagsQuery()
    {
        Tag::factory()->count(50)->create();

        $tags = (new Tag)->getPopularTags();

        $this->assertTrue(Cache::has('popular_tags_1'));
    }

    public function testItInvalidatesCacheWhenTagIsSaved()
    {
        $tag = Tag::factory()->create(['popularity_score' => 100]);

        $tag->update(['popularity_score' => 200]);

        $this->assertFalse(Cache::has('popular_tags_1'));
    }

    public function testItInvalidatesCacheWhenTagIsDeleted()
    {
        $tag = Tag::factory()->create(['popularity_score' => 100]);


        $tag->delete();

        $this->assertFalse(Cache::has('popular_tags_1'));
    }

    public function testItthrowsInvalidArgumentExceptionForInvalidLimit()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getPopularTags(-10);
    }

    public function testItFetchesUserTagsWithDefaultPagination()
    {
        $user = User::factory()->create();
        Tag::factory()->count(100)->create(['created_by' => $user->id]);

        $tags = (new Tag)->getUserTags($user->id);

        $this->assertNotEmpty($tags);
        $this->assertCount(50, $tags);
    }

    public function testItCachesTheUserTagsQuery()
    {
        $user = User::factory()->create();
        Tag::factory()->count(50)->create(['created_by' => $user->id]);

        $tags = (new Tag)->getUserTags($user->id);

        $this->assertTrue(Cache::has('user_tags_' . $user->id . '_1'));
    }

    public function testItInvalidatesCacheWhenTagForUserTagQueryIsSaved()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['created_by' => $user->id]);

        $tag->update(['name' => 'Updated Name']);

        $this->assertFalse(Cache::has('user_tags_' . $user->id . '_1'));
    }

    public function testItInvalidatesCacheWhenTagUserTagQueryIsDeleted()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['created_by' => $user->id]);

        $tag->delete();

        $this->assertFalse(Cache::has('user_tags_' . $user->id . '_1'));
    }

    public function testItThrowsInvalidArgumentExceptionForInvalidLimitTagQuery()
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getUserTags($user->id, -10);
    }

    public function testFetchesTagsWithDefaultPagination()
    {
        Tag::factory()->count(100)->create();

        $request = new Request();
        $tags = (new Tag)->searchTags($request);

        $this->assertNotEmpty($tags);
        $this->assertCount(50, $tags);
    }

    public function testAppliesNameFilter()
    {
        Tag::factory()->create(['name' => 'SpecificTag']);
        Tag::factory()->count(10)->create();

        $request = new Request(['name' => 'SpecificTag']);
        $tags = (new Tag)->searchTags($request);

        $this->assertCount(1, $tags);
        $this->assertEquals('SpecificTag', $tags[0]->name);
    }

    public function testAppliesTagTypeFilter()
    {
        Tag::factory()->create(['tag_type' => 'SpecificType']);
        Tag::factory()->count(10)->create();

        $request = new Request(['tag_type' => 'SpecificType']);
        $tags = (new Tag)->searchTags($request);

        $this->assertCount(1, $tags);
        $this->assertEquals('SpecificType', $tags[0]->tag_type);
    }

    public function testAppliesIsActiveFilter()
    {
        $inactiveTag = Tag::factory()->create(['is_active' => false]);

        Tag::factory()->create(['is_active' => true]);

        $request = new Request(['is_active' => false]);
        $tags = (new Tag)->searchTags($request);

        $this->assertCount(1, $tags);
        $this->assertFalse((bool) $tags[0]->is_active); // Ensure proper boolean context is used
        $this->assertEquals($inactiveTag->id, $tags[0]->id);
    }


    public function testHandlesModelNotFoundException()
    {
        $this->expectException(ModelNotFoundException::class);

        $request = new Request(['name' => 'NonExistentTag']);
        (new Tag)->searchTags($request);
    }

    public function testGetInactiveTagsReturnsInactiveTags()
    {
        Tag::factory()->create(['is_active' => false]);
        Tag::factory()->create(['is_active' => true]);

        $request = new Request();
        $tags = (new Tag)->getInactiveTags($request);

        $this->assertCount(1, $tags);
        $this->assertFalse((bool) $tags[0]->is_active); // Ensure proper boolean context is used
    }

    public function testGetInactiveTagsHandlesNoResults()
    {
        $request = new Request();
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getInactiveTags($request);
    }

    public function testGetInactiveTagsHandlesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        // Sending an invalid value for is_active
        $request = new Request(['is_active' => 'invalid']);
        (new Tag)->getInactiveTags($request);
    }


    public function testGetInactiveTagsPagination()
    {
        Tag::factory()->count(60)->create(['is_active' => false]);

        $request = new Request();
        $tags = (new Tag)->getInactiveTags($request);

        $this->assertCount(50, $tags);
        $this->assertTrue(count($tags) > 0);
    }

}