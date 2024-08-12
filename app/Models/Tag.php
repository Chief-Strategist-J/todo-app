<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'is_active',
        'order',
        'version',
        'follower_count',
        'usage_count',
        'related_posts_count',
        'user_interaction_count',
        'popularity_score',
        'name',
        'slug',
        'meta_title',
        'color',
        'image_url',
        'tag_type',
        'content_type',
        'description_vector',
        'meta_description',
        'description',
        'geolocation_data',
        'meta_data',
        'created_by',
        'parent_id',
        'todo_id',
        'last_trend_update',
        'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'version' => 'integer',
        'follower_count' => 'integer',
        'usage_count' => 'integer',
        'related_posts_count' => 'integer',
        'user_interaction_count' => 'integer',
        'popularity_score' => 'decimal:2',
        'geolocation_data' => 'json',
        'meta_data' => 'json',
        'last_trend_update' => 'datetime',
        'last_used_at' => 'datetime'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($tag) {
            Cache::forget("tags_for_task_{$tag->todo_id}_page_1");
            Cache::forget('popular_tags_' . $tag->id);
            Cache::forget('user_tags');
            Tag::cacheTags();
        });

        static::updated(function ($tag) {
            Cache::forget("tags_for_task_{$tag->todo_id}_page_1");
            Cache::forget('popular_tags_' . $tag->id);
            Cache::forget('user_tags');
            Tag::cacheTags();
        });

        static::deleted(function ($tag) {
            Cache::forget("tags_for_task_{$tag->todo_id}_page_1");
            Cache::forget('popular_tags_' . $tag->id);
            Cache::forget('user_tags');
            Tag::cacheTags();

        });
    }



    public function createTag(Request $request, $taskId): bool|int
    {


        $tag = new Tag();

        foreach ($this->fillable as $field) {
            if ($request->has($field)) {
                $tag->$field = $request->input($field);
            }
        }

        return $tag->save();
    }

    public function updateTag(Request $request): int
    {
        $tagId = $request->input('id');
        $tag = Tag::where('id', $tagId)->first();

        if (!$tag) {
            return 0; // Return 0 if the tag is not found
        }

        foreach ($this->fillable as $field) {
            if ($request->has($field)) {
                $tag->$field = $request->input($field);
            }
        }

        return $tag->save();
    }


    public function deleteTag($id): int
    {
        return DB::table('tags')->where('id', $id)->delete();
    }

    public function getTagsByTaskId($taskId, $page = 1)
    {
        // Validate the task ID
        if (!is_numeric($taskId)) {
            throw new InvalidArgumentException("Invalid Task ID");
        }

        if ($page < 1) {
            throw new InvalidArgumentException("Invalid pagination page number");
        }

        $taskExists = DB::table('todos')->where('id', $taskId)->exists();

        if (!$taskExists) {
            throw new ModelNotFoundException("Task not found");
        }

        // Attempt to get cached tags
        $cacheKey = "tags_for_task_{$taskId}_page_{$page}";
        $results = Cache::remember($cacheKey, 10080, function () use ($taskId) {
            return DB::table('tags')
                ->select('id', 'name', 'slug', 'todo_id')
                ->where('todo_id', $taskId)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->paginate(50)->items();
        });

        return $results;
    }

    public function getPopularTags($limit = 50, $page = 1): array
    {
        if (!is_numeric($limit) || $limit <= 0 || $limit > 50) {
            throw new InvalidArgumentException('Limit must be a positive number and less than or equal to 50.');
        }

        $cacheKey = 'popular_tags_' . $page;

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($limit) {
            return DB::table('tags')
                ->select('id', 'name', 'popularity_score')
                ->whereNull('deleted_at')
                ->orderBy('popularity_score', 'desc')
                ->paginate($limit)
                ->items();
        });
    }

    public function getUserTags(int $userId, int $limit = 50, int $page = 1): array
    {
        if (!is_numeric($limit) || $limit <= 0 || $limit > 50) {
            throw new InvalidArgumentException('Limit must be a positive number and less than or equal to 50.');
        }

        $cacheKey = 'user_tags_' . $userId . '_' . $page;

        try {
            return Cache::remember($cacheKey, now()->addWeek(), function () use ($userId, $limit) {
                return DB::table('tags')
                    ->select('id', 'name', 'created_by')
                    ->where('created_by', $userId)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'desc')
                    ->paginate($limit)
                    ->items();
            });
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('Tags not found for the given user.');
        } catch (QueryException $e) {
            throw new QueryException($e->getConnectionName(), $e->getSql(), $e->getBindings(), $e->getPrevious());
        } catch (Exception $e) {
            throw new Exception('An unexpected error occurred.');
        }
    }

    public function searchTags(Request $request): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'slug', 'tag_type', 'is_active')
                ->whereNull('deleted_at');

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->has('tag_type')) {
                $query->where('tag_type', $request->input('tag_type'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->input('is_active'));
            }

            $query->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found.');
            }

            return $result->items();

        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }


    public function getInactiveTags(Request $request): array
    {
        try {
            // Validate request parameters
            if ($request->has('is_active') && !is_bool($request->input('is_active'))) {
                throw new InvalidArgumentException('Invalid argument provided.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'is_active')
                ->where('is_active', false)
                ->whereNull('deleted_at');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No inactive tags found.');
            }

            return $result->items();
        } catch (InvalidArgumentException $e) {
            throw $e; // Rethrow to preserve the exception message
        } catch (ModelNotFoundException $e) {
            throw $e; // Rethrow to preserve the exception message
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching inactive tags.');
        }
    }



    public function getTagsByParentId(int $parentId): array
    {
        try {
            if ($parentId < 0) {
                throw new InvalidArgumentException('Invalid parent ID provided.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'parent_id')
                ->where('parent_id', $parentId)
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given parent ID.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given parent ID.');
        } catch (InvalidArgumentException $e) {
            throw $e; // Rethrow to preserve the exception message
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getTagsOrderedByCreatedAt(int $limit = 50): array
    {
        try {
            if ($limit <= 0 || $limit > 50) {
                throw new InvalidArgumentException('Invalid limit provided. Must be between 1 and 50.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'created_at')
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc');

            $result = $query->paginate($limit);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found.');
        } catch (InvalidArgumentException $e) {
            throw $e; // Rethrow to preserve the exception message
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getPopularTag(int $popularityThreshold, int $usageThreshold, int $limit = 50): array
    {
        try {
            if ($popularityThreshold < 0 || $usageThreshold < 0 || $limit <= 0 || $limit > 50) {
                throw new InvalidArgumentException('Invalid arguments provided.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'popularity_score', 'usage_count')
                ->where('popularity_score', '>=', $popularityThreshold)
                ->where('usage_count', '>=', $usageThreshold)
                ->whereNull('deleted_at')
                ->orderBy('popularity_score', 'desc')
                ->orderBy('usage_count', 'desc');

            $result = $query->paginate($limit);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found matching the criteria.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found matching the criteria.');
        } catch (InvalidArgumentException $e) {
            throw $e; // Rethrow to preserve the exception message
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching popular tags.');
        }
    }


    public function getTagsWithParentAndChild(int $tagId): \Illuminate\Http\JsonResponse
    {
        try {
            $tag = DB::table('tags')
                ->select('id', 'name', 'parent_id')
                ->where('id', $tagId)
                ->whereNull('deleted_at')
                ->first();

            if (!$tag) {
                throw new ModelNotFoundException('Tag not found.');
            }

            $parent = DB::table('tags')
                ->select('id', 'name')
                ->where('id', $tag->parent_id)
                ->whereNull('deleted_at')
                ->first();

            $children = DB::table('tags')
                ->select('id', 'name')
                ->where('parent_id', $tagId)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->paginate(50);

            return response()->json(['tag' => $tag, 'parent' => $parent, 'children' => $children], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => 'Invalid argument provided.'], 400);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching tags.'], 500);
        }
    }

    public function getTagsByDateRange(string $startDate, string $endDate): array
    {
        try {
            if (!Carbon::hasFormat($startDate, 'Y-m-d') || !Carbon::hasFormat($endDate, 'Y-m-d')) {
                throw new InvalidArgumentException('Invalid date format provided.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'created_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given date range.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given date range.');
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getTagsByPopularityScore(float $minScore, float $maxScore): array
    {
        try {
            // Validate input scores
            if ($minScore > $maxScore) {
                throw new InvalidArgumentException('Minimum score must be less than or equal to maximum score.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'popularity_score')
                ->whereBetween('popularity_score', [$minScore, $maxScore])
                ->whereNull('deleted_at')
                ->orderBy('popularity_score', 'desc');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given popularity score range.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given popularity score range.');
        } catch (InvalidArgumentException $e) {
            throw $e; // Rethrow to preserve the exception message
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getTagsByUsageCountRange(int $minCount, int $maxCount): array
    {
        try {
            if ($minCount > $maxCount) {
                throw new InvalidArgumentException('Minimum count must be less than or equal to maximum count.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'usage_count')
                ->whereBetween('usage_count', [$minCount, $maxCount])
                ->whereNull('deleted_at')
                ->orderBy('usage_count', 'desc');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given usage count range.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given usage count range.');
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getActiveTags(): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'is_active')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No active tags found.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No active tags found.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching active tags.');
        }
    }



    public function getTagsByTaskIdAndStatus(int $taskId, bool $status): array
    {
        try {
            if ($taskId <= 0 || !is_bool($status)) {
                throw new InvalidArgumentException('Invalid argument provided.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'todo_id', 'is_active')
                ->where('todo_id', $taskId)
                ->where('is_active', $status)
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given task ID and status.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getTagsByColor(string $color): array
    {
        try {
            if (!is_string($color)) {
                throw new InvalidArgumentException('Invalid color argument provided.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'color')
                ->where('color', $color)
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given color.');
            }

            return $result->items();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid color argument provided.');
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given color.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags.');
        }
    }

    public function getTagsWithMetaTitle(): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'meta_title')
                ->whereNotNull('meta_title')
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found with a meta title.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found with a meta title.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags with meta titles.');
        }
    }

    public function getTagsWithGeolocationData(): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'geolocation_data')
                ->whereNotNull('geolocation_data')
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found with geolocation data.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found with geolocation data.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags with geolocation data.');
        }
    }


    public function getTagsByContentType(string $contentType): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'content_type')
                ->where('content_type', $contentType)
                ->whereNull('deleted_at')
                ->orderBy('name');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given content type.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given content type.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags by content type.');
        }
    }


    public function getTagsWithDescriptionVector(): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'description_vector', 'created_at')
                ->whereNotNull('description_vector')
                ->whereNull('deleted_at')
                ->orderBy('created_at');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found with a description vector.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found with a description vector.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags with a description vector.');
        }
    }

    public function getTagsByCreatedDateRange(string $startDate, string $endDate): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'created_at', 'updated_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->orderBy('created_at');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given date range.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given date range.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags by created date range.');
        }
    }


    public function getTagsByUpdatedDateRange(string $startDate, string $endDate): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'created_at', 'updated_at')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->orderBy('updated_at');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given updated date range.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given updated date range.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags by updated date range.');
        }
    }


    public function getTagsWithMetaData(): array
    {
        try {
            $query = DB::table('tags')
                ->select('id', 'name', 'meta_data', 'created_at')
                ->whereNotNull('meta_data')
                ->whereNull('deleted_at')
                ->orderBy('created_at');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found with meta data.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found with meta data.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags with meta data.');
        }
    }

    public function getTagsByVersion(string $version): array
    {
        try {
            if (empty($version)) {
                throw new InvalidArgumentException('Version must not be empty.');
            }

            $query = DB::table('tags')
                ->select('id', 'name', 'version', 'created_at')
                ->where('version', $version)
                ->whereNull('deleted_at')
                ->orderBy('created_at');

            $result = $query->paginate(50);

            if ($result->isEmpty()) {
                throw new ModelNotFoundException('No tags found for the given version.');
            }

            return $result->items();
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException('No tags found for the given version.');
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            throw new Exception('An error occurred while fetching tags by version.');
        }
    }

    public function getSeededTags(int $page = 1): array
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page number must be greater than 0.');
        }

        try {
            $key = Tag::generateCacheKey($page);

            return Cache::remember($key, now()->addWeek(), function () use ($page) {
                return DB::table('tags AS t')
                    ->select('t.id', 't.name', 't.slug', 't.created_by','t.color')
                    ->whereIn('t.name', [
                        'Urgent',
                        'Personal',
                        'Work',
                        'Home',
                        'Important',
                        'Design',
                        'Research', 
                        'Productive'
                    ])
                    ->whereNull('t.deleted_at')
                    ->orderBy('t.name')
                    ->paginate(50, ['*'], 'page', $page)
                    ->items();
            });

        } catch (ModelNotFoundException $e) {
            Log::error('Tag model not found: ' . $e->getMessage());
            throw new ModelNotFoundException('The requested tag does not exist.');
        } catch (InvalidArgumentException $e) {
            Log::error('Invalid argument: ' . $e->getMessage());
            throw new InvalidArgumentException('Invalid argument provided.');
        } catch (Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function cacheTags(): void
    {
        Cache::forget(Tag::generateCacheKey());
    }

    public static function generateCacheKey(int $page = 1): string
    {
        return 'seeded_tags_page_' . $page;
    }

    public function todos()
    {
        return $this->belongsToMany(Todo::class, 'tag_todo', 'tag_id', 'todo_id');
    }
}