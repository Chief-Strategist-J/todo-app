<?php

use App\Http\Requests\BulkCreateTagsRequest;
use App\Models\Tag;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

// php artisan test --filter UserTest
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

    public function testGetTagsByTaskId_InvalidTaskId()
    {
        $this->expectException(InvalidArgumentException::class);
        (new Tag())->getTagsByTaskId('invalid'); // Pass an invalid ID
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

    public function testGetTagsByParentIdHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $request = new Request();
        $tags = (new Tag)->getTagsByParentId(999); // Assuming 999 doesn't exist
    }

    public function testGetTagsByParentIdHandlesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new Request();
        $tags = (new Tag)->getTagsByParentId(-1); // Invalid argument
    }

    public function testGetTagsOrderedByCreatedAtHandlesInvalidLimit()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getTagsOrderedByCreatedAt(0); // Invalid limit
    }

    public function testGetTagsOrderedByCreatedAtReturnsItems()
    {
        Tag::factory()->count(3)->create(['deleted_at' => null]);

        $tags = (new Tag)->getTagsOrderedByCreatedAt(2);

        $this->assertCount(2, $tags);
    }

    public function testGetTagsOrderedByCreatedAtHandlesNoTagsFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $tags = (new Tag)->getTagsOrderedByCreatedAt(10); // Assuming there are no tags
    }



    public function testGetPopularTagsHandlesInvalidArguments()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getPopularTag(-1, 10); // Invalid popularity threshold
    }

    public function testGetPopularTagsReturnsItems()
    {
        Tag::factory()->count(3)->create([
            'popularity_score' => 100,
            'usage_count' => 50,
            'deleted_at' => null
        ]);

        $tags = (new Tag)->getPopularTag(50, 20, 2);

        $this->assertCount(2, $tags);
    }

    public function testGetPopularTagsHandlesNoTagsFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $tags = (new Tag)->getPopularTag(1000, 1000, 10); // Assuming there are no tags matching the criteria
    }


    public function testGetTagsWithParentAndChildHandlesTagNotFound()
    {
        $response = (new Tag)->getTagsWithParentAndChild(999); // Assuming 999 does not exist

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Tag not found.', $response->getData()->error);
    }

    public function testGetTagsWithParentAndChildReturnsData()
    {
        $parentTag = Tag::factory()->create();
        $tag = Tag::factory()->create(['parent_id' => $parentTag->id]);
        $childTag = Tag::factory()->create(['parent_id' => $tag->id]);

        $response = (new Tag)->getTagsWithParentAndChild($tag->id);

        $this->assertEquals($tag->id, $response->getData()->tag->id);
        $this->assertEquals($parentTag->id, $response->getData()->parent->id);
        $this->assertCount(1, $response->getData()->children->data); // Updated to check children data
        $this->assertEquals($childTag->id, $response->getData()->children->data[0]->id);
    }


    public function testGetTagsByDateRangeReturnsData()
    {
        Tag::factory()->create(['created_at' => now()->subDays(5)]);
        Tag::factory()->create(['created_at' => now()->subDays(3)]);

        $request = new Request();
        $tags = (new Tag)->getTagsByDateRange(now()->subDays(7)->toDateString(), now()->toDateString());

        $this->assertCount(2, $tags);
    }

    public function testGetTagsByDateRangeHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $request = new Request();
        (new Tag)->getTagsByDateRange(now()->subDays(1)->toDateString(), now()->subDays(2)->toDateString());
    }

    public function testGetTagsByDateRangeHandlesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        $request = new Request();
        (new Tag)->getTagsByDateRange('invalid-date', 'another-invalid-date');
    }

    public function testGetTagsByPopularityScoreReturnsData()
    {
        Tag::factory()->create(['popularity_score' => 10]);
        Tag::factory()->create(['popularity_score' => 20]);

        $tags = (new Tag)->getTagsByPopularityScore(5, 25);

        $this->assertCount(2, $tags);
    }

    public function testGetTagsByPopularityScoreHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByPopularityScore(50, 100);
    }

    public function testGetTagsByPopularityScoreHandlesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getTagsByPopularityScore(30, 10);
    }


    public function testGetTagsByUsageCountRangeReturnsData()
    {
        Tag::factory()->create(['usage_count' => 5]);
        Tag::factory()->create(['usage_count' => 10]);

        $tags = (new Tag)->getTagsByUsageCountRange(0, 15);

        $this->assertCount(2, $tags);
    }

    public function testGetTagsByUsageCountRangeHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByUsageCountRange(50, 100);
    }

    public function testGetTagsByUsageCountRangeHandlesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getTagsByUsageCountRange(30, 10);
    }


    public function testGetActiveTagsReturnsData()
    {
        Tag::factory()->create(['is_active' => true]);
        Tag::factory()->create(['is_active' => false]);

        $tags = (new Tag)->getActiveTags();

        $this->assertCount(1, $tags);
    }

    public function testGetActiveTagsHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getActiveTags();
    }

    public function testGetActiveTagsOrderBy()
    {
        Tag::factory()->create(['is_active' => true, 'name' => 'B']);
        Tag::factory()->create(['is_active' => true, 'name' => 'A']);

        $tags = (new Tag)->getActiveTags();

        $this->assertEquals('A', $tags[0]->name);
    }

    public function testGetActiveTagsHandlesGeneralException()
    {
        $this->expectException(Exception::class);

        // Simulate an error by overriding the query method
        $this->app['db']->shouldReceive('table')->andThrow(new Exception('DB Error'));

        (new Tag)->getActiveTags();
    }


    public function testGetTagsByTaskIdAndStatusReturnsData()
    {
        $taskId = 1;
        Tag::factory()->create(['todo_id' => $taskId, 'is_active' => true]);
        Tag::factory()->create(['todo_id' => $taskId, 'is_active' => false]);

        $tags = (new Tag)->getTagsByTaskIdAndStatus($taskId, true);

        $this->assertCount(1, $tags);
    }


    public function testGetTagsByTaskIdAndStatusHandlesInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Tag)->getTagsByTaskIdAndStatus(-1, true); // Invalid taskId
    }

    public function testGetTagsByTaskIdAndStatusReturnsEmptyArrayForInactiveTags()
    {
        $taskId = 1;
        Tag::factory()->create(['todo_id' => $taskId, 'is_active' => false]);

        $tags = (new Tag)->getTagsByTaskIdAndStatus($taskId, true);

        $this->assertEmpty($tags);
    }

    public function testGetTagsByTaskIdAndStatusOrderBy()
    {
        $taskId = 1;
        Tag::factory()->create(['todo_id' => $taskId, 'is_active' => true, 'name' => 'B']);
        Tag::factory()->create(['todo_id' => $taskId, 'is_active' => true, 'name' => 'A']);

        $tags = (new Tag)->getTagsByTaskIdAndStatus($taskId, true);

        $this->assertEquals('A', $tags[0]->name);
    }


    public function testGetTagsByColorHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByColor('nonexistentColor');
    }

    public function testGetTagsByColorReturnsData()
    {
        $color = 'blue';
        Tag::factory()->create(['color' => $color]);

        $tags = (new Tag)->getTagsByColor($color);

        $this->assertNotEmpty($tags);
        $this->assertEquals($color, $tags[0]->color);
    }


    public function testGetTagsByColorReturnsOrderedResults()
    {
        $color = 'red';
        Tag::factory()->create(['color' => $color, 'name' => 'B']);
        Tag::factory()->create(['color' => $color, 'name' => 'A']);

        $tags = (new Tag)->getTagsByColor($color);

        $this->assertEquals('A', $tags[0]->name);
        $this->assertEquals('B', $tags[1]->name);
    }


    public function testGetTagsWithMetaTitleHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsWithMetaTitle();
    }



    public function testGetTagsWithMetaTitleReturnsData()
    {
        Tag::factory()->create(['meta_title' => 'Example Title']);

        $tags = (new Tag)->getTagsWithMetaTitle();

        $this->assertNotEmpty($tags);
        $this->assertEquals('Example Title', $tags[0]->meta_title);
    }

    public function testGetTagsWithGeolocationDataReturnsData()
    {
        Tag::factory()->count(5)->create(['geolocation_data' => 'lat:123, long:456']);

        $tags = (new Tag)->getTagsWithGeolocationData();

        $this->assertCount(5, $tags);
    }

    public function testGetTagsWithGeolocationDataHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsWithGeolocationData();
    }

    public function testGetTagsWithGeolocationDataHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsWithGeolocationData();
    }

    public function testGetTagsByContentTypeReturnsData()
    {
        Tag::factory()->count(5)->create(['content_type' => 'type1']);

        $tags = (new Tag)->getTagsByContentType('type1');

        $this->assertCount(5, $tags);
    }

    public function testGetTagsByContentTypeHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByContentType('non-existent-type');
    }

    public function testGetTagsByContentTypeHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsByContentType('type1');
    }

    public function testGetTagsWithDescriptionVectorReturnsData()
    {
        Tag::factory()->count(5)->create(['description_vector' => 'some vector data']);

        $tags = (new Tag)->getTagsWithDescriptionVector();

        $this->assertCount(5, $tags);
    }

    public function testGetTagsWithDescriptionVectorHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsWithDescriptionVector();
    }

    public function testGetTagsWithDescriptionVectorHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsWithDescriptionVector();
    }

    public function testGetTagsByCreatedDateRangeReturnsData()
    {
        Tag::factory()->count(5)->create(['created_at' => now()->subDays(5)]);
        Tag::factory()->count(5)->create(['created_at' => now()->subDays(10)]);

        $tags = (new Tag)->getTagsByCreatedDateRange(now()->subDays(7)->toDateString(), now()->toDateString());

        $this->assertCount(5, $tags);
    }

    public function testGetTagsByCreatedDateRangeHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByCreatedDateRange(now()->toDateString(), now()->toDateString());
    }

    public function testGetTagsByCreatedDateRangeHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsByCreatedDateRange(now()->subDays(10)->toDateString(), now()->toDateString());
    }

    public function testGetTagsByUpdatedDateRangeReturnsData()
    {
        Tag::factory()->count(5)->create(['updated_at' => now()->subDays(5)]);
        Tag::factory()->count(5)->create(['updated_at' => now()->subDays(10)]);

        $tags = (new Tag)->getTagsByUpdatedDateRange(now()->subDays(7)->toDateString(), now()->toDateString());

        $this->assertCount(5, $tags);
    }

    public function testGetTagsByUpdatedDateRangeHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByUpdatedDateRange(now()->toDateString(), now()->toDateString());
    }

    public function testGetTagsByUpdatedDateRangeHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsByUpdatedDateRange(now()->subDays(10)->toDateString(), now()->toDateString());
    }

    public function testGetTagsWithMetaDataReturnsData()
    {
        Tag::factory()->count(5)->create(['meta_data' => 'Some meta data']);

        $tags = (new Tag)->getTagsWithMetaData();

        $this->assertCount(5, $tags);
    }

    public function testGetTagsWithMetaDataHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsWithMetaData();
    }

    public function testGetTagsWithMetaDataHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsWithMetaData();
    }
    public function testGetTagsByVersionReturnsData()
    {
        $version = '1.0';
        Tag::factory()->count(5)->create(['version' => $version]);

        $tags = (new Tag)->getTagsByVersion($version);

        $this->assertCount(5, $tags);
    }

    public function testGetTagsByVersionHandlesModelNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        (new Tag)->getTagsByVersion('non-existent-version');
    }

    public function testGetTagsByVersionHandlesQueryError()
    {
        $this->expectException(Exception::class);

        DB::shouldReceive('table')->andThrow(new Exception('Database error'));

        (new Tag)->getTagsByVersion('1.0');
    }

    public function testGetSeededTags(): void
    {
        // Seed tags
        Tag::factory()->create(['name' => 'Urgent']);
        Tag::factory()->create(['name' => 'Personal']);
        Tag::factory()->create(['name' => 'Work']);
        Tag::factory()->create(['name' => 'Home']);
        Tag::factory()->create(['name' => 'Important']);
        Tag::factory()->create(['name' => 'Design']);
        Tag::factory()->create(['name' => 'Research']);
        Tag::factory()->create(['name' => 'Productive']);

        // Test valid page request
        $tags = (new Tag())->getSeededTags(1);
        $this->assertNotEmpty($tags);

        // Test invalid page request
        $this->expectException(ModelNotFoundException::class);
        (new Tag())->getSeededTags(9999);

        // Test invalid argument exception
        $this->expectException(InvalidArgumentException::class);
        (new Tag())->getSeededTags(0);
    }

}