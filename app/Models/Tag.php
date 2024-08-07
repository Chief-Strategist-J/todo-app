<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
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
            self::clearCache($tag->todo_id);
        });

        static::updated(function ($tag) {
            self::clearCache($tag->todo_id);
        });

        static::deleted(function ($tag) {
            self::clearCache($tag->todo_id);
        });
    }



    public function createTag(Request $request, $taskId): bool|int
    {
        $fillable = [
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
            'last_trend_update',
            'last_used_at'
        ];

        $tag = new Tag();

        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $tag->$field = $request->input($field);
            }
        }

        return $tag->save();
    }

    private function updateTag(Request $request, $taskId): int
    {
        $fillable = [
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
            'last_trend_update',
            'last_used_at'
        ];

        $tagId = $request->input('id');

        $tag = DB::table('tags')->where('id', $tagId)->first();

        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $tag->$field = $request->input($field);
            }
        }

        return DB::table('tags')->where('id', $tagId)->update((array) $tag);
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


    protected static function clearCache($taskId)
    {
        Cache::forget("tags_for_task_{$taskId}_page_1");
    }


    public function getPopularTags($limit = 10): Collection
    {
        return DB::table('tags')->select('id', 'name', 'popularity_score')
            ->orderBy('popularity_score', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTagsByUser($userId): Collection
    {
        return DB::table('tags')->select('id', 'name', 'created_by')->where('created_by', $userId)->get();
    }

    public function searchTags(Request $request): Collection
    {
        $query = DB::table('tags')->select('id', 'name', 'slug', 'tag_type', 'is_active');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('tag_type')) {
            $query->where('tag_type', $request->input('tag_type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->input('is_active'));
        }

        return $query->get();
    }

    public function getInactiveTags(): Collection
    {
        return DB::table('tags')->select('id', 'name', 'is_active')->where('is_active', false)->get();
    }

    public function getTagsByParentId($parentId): Collection
    {
        return DB::table('tags')->select('id', 'name', 'parent_id')->where('parent_id', $parentId)->get();
    }

    public function getRecentTags($limit = 10): Collection
    {
        return DB::table('tags')->select('id', 'name', 'created_at')->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function getTagsByPopularityAndUsage($popularityThreshold, $usageThreshold): Collection
    {
        return DB::table('tags')->select('id', 'name', 'popularity_score', 'usage_count')->where('popularity_score', '>=', $popularityThreshold)->where('usage_count', '>=', $usageThreshold)->get();
    }


    public function getTagsWithParentAndChild($tagId): \Illuminate\Http\JsonResponse
    {
        $tag = DB::table('tags')->select('id', 'name', 'parent_id')->where('id', $tagId)->first();
        $parent = $tag ? DB::table('tags')->select('id', 'name')->where('id', $tag->parent_id)->first() : null;
        $children = $tag ? DB::table('tags')->select('id', 'name')->where('parent_id', $tagId)->get() : collect();

        return response()->json(['tag' => $tag, 'parent' => $parent, 'children' => $children], 200);
    }

    public function getTagsByDateRange($startDate, $endDate): Collection
    {
        return DB::table('tags')->select('id', 'name', 'created_at')->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getTagsByPopularityScoreRange($minScore, $maxScore): Collection
    {
        return DB::table('tags')->select('id', 'name', 'popularity_score')->whereBetween('popularity_score', [$minScore, $maxScore])->get();
    }

    public function getTagsByUsageCountRange($minCount, $maxCount): Collection
    {
        return DB::table('tags')->select('id', 'name', 'usage_count')->whereBetween('usage_count', [$minCount, $maxCount])->get();
    }

    public function getTagsWithActiveStatus(): Collection
    {
        return DB::table('tags')->select('id', 'name', 'is_active')->where('is_active', true)->get();
    }

    public function getTagsByTaskIdAndStatus($taskId, $status): Collection
    {
        return DB::table('tags')->select('id', 'name', 'task_id', 'is_active')->where('task_id', $taskId)->where('is_active', $status)->get();
    }

    public function getTagsByColor($color): Collection
    {
        return DB::table('tags')->select('id', 'name', 'color')->where('color', $color)->get();
    }

    public function getTagsWithMetaTitle(): Collection
    {
        return DB::table('tags')->select('id', 'name', 'meta_title')->whereNotNull('meta_title')->get();
    }

    public function getTagsWithGeolocationData(): Collection
    {
        return DB::table('tags')->select('id', 'name', 'geolocation_data')->whereNotNull('geolocation_data')->get();
    }

    public function getTagsByContentType($contentType): Collection
    {
        return DB::table('tags')->select('id', 'name', 'content_type')->where('content_type', $contentType)->get();
    }

    public function getTagsWithDescriptionVector(): Collection
    {
        return DB::table('tags')->select('id', 'name', 'description_vector', 'created_at')->whereNotNull('description_vector')->get();
    }


    public function getTagsByCreatedDateRange($startDate, $endDate): Collection
    {
        return DB::table('tags')->select('id', 'name', 'created_at', 'updated_at')->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getTagsByUpdatedDateRange($startDate, $endDate): Collection
    {
        return DB::table('tags')->select('id', 'name', 'created_at', 'updated_at')->whereBetween('updated_at', [$startDate, $endDate])->get();
    }

    public function getTagsWithMetaData(): Collection
    {
        return DB::table('tags')->select('id', 'name', 'meta_data', 'created_at')->whereNotNull('meta_data')->get();
    }

    public function getTagsByVersion($version): Collection
    {
        return DB::table('tags')->select('id', 'name', 'version', 'created_at')->where('version', $version)->get();
    }

    public function getTagsWithVersionGreaterThan($version): Collection
    {
        return DB::table('tags')->select('id', 'name', 'version', 'created_at')->where('version', '>', $version)->get();
    }

    public function getTagsWithVersionLessThan($version): Collection
    {
        return DB::table('tags')->select('id', 'name', 'version', 'created_at')->where('version', '<', $version)->get();
    }

    public function todos()
    {
        return $this->belongsToMany(Todo::class, 'tag_todo', 'tag_id', 'todo_id');
    }
}