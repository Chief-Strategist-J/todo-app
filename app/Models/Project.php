<?php

namespace App\Models;

use App\Http\Requests\StoreProjectRequest;
use App\Jobs\FetchPaginatedTodos;

use Exception;
use InvalidArgumentException;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use function App\Helper\errorMsg;
use function App\Helper\getIndianTime;
use function App\Helper\successMessage;



class Project extends Model
{
    use HasFactory, SoftDeletes;

    // Table name
    protected $table = 'projects';

    // Fillable fields
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget',
        'currency',
        'progress_percentage',
        'priority',
        'is_public',
        'client_name',
        'project_manager',
        'estimated_hours',
        'actual_hours',
        'repository_url',
        'documentation_url',
        'category',
        'is_archived',
        'task_count',
        'completed_task_count',
        'team_size',
        'last_activity_at',
        'project_code',
        'risk_score',
        'status_color',
        'comment_count',
        'attachment_count',
        'completion_percentage',
        'main_language',
        'is_featured',
        'customer_satisfaction_score',
        'revision_count',
        'project_type',
        'roi',
        'stakeholder_count',
        'budget_utilization',
        'project_phase',
        'lessons_learned',
        'created_by',
        'updated_by',
        'department_id',
    ];

    // Cast attributes to their appropriate types
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
        'risk_score' => 'decimal:2',
        'completion_percentage' => 'decimal:2',
        'customer_satisfaction_score' => 'decimal:2',
        'roi' => 'decimal:2',
        'budget_utilization' => 'decimal:2',
        'is_public' => 'boolean',
        'is_archived' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array', // Assuming metadata is a JSON field
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createProject(array $data)
    {
        return DB::transaction(function () use ($data) {
            $existingProject = DB::table('projects')->where('name', $data['name'])->first();

            if ($existingProject) {
                throw new Exception('A project with the same name already exists.');
            }

            return DB::table('projects')->insertGetId($data);
        });
    }

    public function assignCategoriesToProject(int $projectId, array $categoryNames): void
    {
        if (empty($categoryNames)) {
            return;
        }

        $uniqueCategoryNames = array_unique($categoryNames);
        $chunkSize = 1000;
        foreach (array_chunk($uniqueCategoryNames, $chunkSize) as $chunkedCategoryNames) {

            DB::table('project_category')->insertUsing(
                ['project_id', 'category_id', 'created_at', 'updated_at'],
                DB::table('project_categories')
                    ->leftJoin('project_category', function ($join) use ($projectId) {
                        $join->on('project_categories.id', '=', 'project_category.category_id')
                            ->where('project_category.project_id', '=', $projectId);
                    })
                    ->select([
                        DB::raw("$projectId as project_id"),
                        'project_categories.id as category_id',
                        DB::raw("CURRENT_TIMESTAMP as created_at"),
                        DB::raw("CURRENT_TIMESTAMP as updated_at")
                    ])
                    ->whereNull('project_category.category_id')
                    ->whereIn('project_categories.name', $chunkedCategoryNames)
            );
        }
    }

    public function assignPhasesToProject(int $projectId, array $phaseNames): void
    {
        if (empty($phaseNames)) {
            return;
        }

        $uniquePhaseNames = array_unique($phaseNames);
        $chunkSize = 1000;
        foreach (array_chunk($uniquePhaseNames, $chunkSize) as $chunkedPhaseNames) {

            DB::table('project_phase')->insertUsing(
                ['project_id', 'phase_id', 'created_at', 'updated_at'],
                DB::table('project_phases')
                    ->leftJoin('project_phase', function ($join) use ($projectId) {
                        $join->on('project_phases.id', '=', 'project_phase.phase_id')
                            ->where('project_phase.project_id', '=', $projectId);
                    })
                    ->select([
                        DB::raw("$projectId as project_id"),
                        'project_phases.id as phase_id',
                        DB::raw("CURRENT_TIMESTAMP as created_at"),
                        DB::raw("CURRENT_TIMESTAMP as updated_at")
                    ])
                    ->whereNull('project_phase.phase_id')
                    ->whereIn('project_phases.name', $chunkedPhaseNames)
            );
        }
    }

    public function assignStatusesToProject(int $projectId, array $statusNames): void
    {
        if (empty($statusNames)) {
            return;
        }

        $uniqueStatusNames = array_unique($statusNames);

        $chunkSize = 1000;
        foreach (array_chunk($uniqueStatusNames, $chunkSize) as $chunkedStatusNames) {

            DB::table('project_project_status')->insertUsing(
                ['project_id', 'status_id', 'created_at', 'updated_at'],
                DB::table('project_statuses')
                    ->leftJoin('project_project_status', function ($join) use ($projectId) {
                        $join->on('project_statuses.id', '=', 'project_project_status.status_id')
                            ->where('project_project_status.project_id', '=', $projectId);
                    })
                    ->select([
                        DB::raw("$projectId as project_id"),
                        'project_statuses.id as status_id',
                        DB::raw("CURRENT_TIMESTAMP as created_at"),
                        DB::raw("CURRENT_TIMESTAMP as updated_at")
                    ])
                    ->whereNull('project_project_status.status_id')
                    ->whereIn('project_statuses.name', $chunkedStatusNames)
            );
        }
    }

    public function assignPrioritiesToProject(int $projectId, array $priorityNames): void
    {
        if (empty($priorityNames)) {
            return;
        }

        // Ensure unique names only, to avoid redundant database operations
        $uniquePriorityNames = array_unique($priorityNames);

        // Chunk the unique priority names to handle large datasets efficiently
        $chunkSize = 1000;  // Adjust chunk size based on your system's capacity
        foreach (array_chunk($uniquePriorityNames, $chunkSize) as $chunkedPriorityNames) {

            DB::table('project_priority')->insertUsing(
                ['project_id', 'priority_id', 'created_at', 'updated_at'],
                DB::table('project_priorities')
                    ->leftJoin('project_priority', function ($join) use ($projectId) {
                        $join->on('project_priorities.id', '=', 'project_priority.priority_id')
                            ->where('project_priority.project_id', '=', $projectId);
                    })
                    ->select([
                        DB::raw("$projectId as project_id"),
                        'project_priorities.id as priority_id',
                        DB::raw("CURRENT_TIMESTAMP as created_at"),  // Use database function for timestamp
                        DB::raw("CURRENT_TIMESTAMP as updated_at")
                    ])
                    ->whereNull('project_priority.priority_id')  // Exclude already assigned priorities
                    ->whereIn('project_priorities.name', $chunkedPriorityNames)
            );
        }
    }

    public function assignTypesToProject(int $projectId, array $typeNames): void
{
    if (empty($typeNames)) {
        return;
    }

    // Ensure unique names only, to avoid redundant database operations
    $uniqueTypeNames = array_unique($typeNames);

    // Chunk the unique type names to handle large datasets efficiently
    $chunkSize = 1000;  // Adjust chunk size based on your system's capacity
    foreach (array_chunk($uniqueTypeNames, $chunkSize) as $chunkedTypeNames) {
        
        DB::table('project_project_type')->insertUsing(
            ['project_id', 'type_id', 'created_at', 'updated_at'],
            DB::table('project_types')
                ->leftJoin('project_project_type', function ($join) use ($projectId) {
                    $join->on('project_types.id', '=', 'project_project_type.type_id')
                         ->where('project_project_type.project_id', '=', $projectId);
                })
                ->select([
                    DB::raw("$projectId as project_id"),
                    'project_types.id as type_id',
                    DB::raw("CURRENT_TIMESTAMP as created_at"),  // Use database function for timestamp
                    DB::raw("CURRENT_TIMESTAMP as updated_at")
                ])
                ->whereNull('project_project_type.type_id')  // Exclude already assigned types
                ->whereIn('project_types.name', $chunkedTypeNames)
        );
    }
}



    public function fetchActiveProjects(int $creatorId): LengthAwarePaginator
    {
        return DB::table('projects')
            ->where('created_by', $creatorId)
            ->whereNull('deleted_at') // For soft deletes
            ->where('is_archived', false)
            ->select(['id', 'uuid', 'name', 'slug', 'description', 'status', 'is_public', 'start_date', 'end_date', 'created_at', 'project_code'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }


    public function updateProject(array $data, int $projectId): void
    {
        DB::table($this->table)
            ->where('id', $projectId)
            ->update($data);
    }

    public function assignTodoToProjects(array $assignments): void
    {
        DB::transaction(function () use ($assignments): void {
            $insertData = [];
            $uniqueEntries = [];

            foreach ($assignments as $assignment) {
                $key = $assignment['project_id'] . '-' . $assignment['todo_id'];

                if (!isset($uniqueEntries[$key])) {
                    $uniqueEntries[$key] = true;

                    $insertData[] = [
                        'project_id' => (int) $assignment['project_id'],
                        'todo_id' => (int) $assignment['todo_id'],
                        'order' => (int) $assignment['order'],
                        'added_at' => now(),
                        'added_by' => (int) $assignment['added_by'],
                        'is_critical_path' => (bool) $assignment['is_critical_path'],
                    ];
                }
            }

            if (!empty($insertData)) {
                DB::table('project_todo')->upsert($insertData, ['project_id', 'todo_id']);
            }
        });
    }


    public function getPaginatedProjectsForTodo(
        int $todoId,
        int $userId,
        int $page = 1,
        int $perPage = 20
    ): LengthAwarePaginator {
        return Cache::flexible("projects_for_todo_user_{$todoId}_{$userId}_page_{$page}", [60, 120], function () use ($todoId, $userId, $page, $perPage): LengthAwarePaginator {
            $query = DB::table('projects')
                ->join('project_todo', 'projects.id', '=', 'project_todo.project_id')
                ->where('project_todo.todo_id', $todoId)
                ->where('project_todo.added_by', $userId)
                ->select(
                    'projects.id',
                    'projects.name',
                    'projects.slug',
                    'projects.description',
                    'projects.status',
                    'projects.project_code'
                )
                ->distinct();

            return $query->paginate($perPage, ['*'], 'page', $page);
        });
    }


    public function getPaginatedTodosForProject(int $projectId, int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return Cache::flexible("todos_for_project_user_{$projectId}_{$userId}_page_{$page}", [60, 120], function () use ($projectId, $userId, $page, $perPage): LengthAwarePaginator {
            $query = DB::table('todos')
                ->join('project_todo', 'todos.id', '=', 'project_todo.todo_id')
                ->where('project_todo.project_id', $projectId)
                ->where('project_todo.added_by', $userId)
                ->select(
                    'todos.id',
                    'todos.title',
                    'todos.description'
                )
                ->distinct();

            return $query->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function deleteProjectAndAssociates(int $projectId): void
    {
        DB::transaction(function () use ($projectId): void {
            $projectId = (int) $projectId;

            $project = DB::table('projects')->where('id', $projectId)->first();

            if (!$project) {
                throw new Exception("Project with ID {$projectId} does not exist.");
            }

            if ($project->is_archived) {
                throw new Exception("Project with ID {$projectId} is archived and cannot be deleted.");
            }


            $tables = [
                'project_user',
                'project_tag',
                'project_todo',
                'project_payments',
                'project_chat_rooms',
                'project_resources',
                'project_stakeholders',
                'project_risk_assessments',
                'project_quality_metrics',
                'project_change_requests',
                'project_milestones',
                'project_dependencies',
                'project_budgets',
                'project_time_logs',
                'project_reports',
                'project_issues',
                'project_documents',
                'project_integrations',
                'project_feedback',
                'project_workflows',
                'project_sprints',
                'project_team_members',
                'project_team_roles',
                'project_cost_centers',
                'project_expenses',
                'project_risks',
                'project_audits',
            ];


            foreach ($tables as $table) {
                DB::table($table)
                    ->where('project_id', $projectId)

                    ->delete();
            }

            DB::table('projects')
                ->where('id', $projectId)
                ->delete();
        });
    }

    public function bulkDeleteProjects(array $projectIds): void
    {
        DB::transaction(function () use ($projectIds): void {
            $projectIds = array_map('intval', $projectIds);

            $nonArchivedProjectIds = DB::table('projects')
                ->whereIn('id', $projectIds)
                ->where('is_archived', false)
                ->pluck('id')
                ->toArray();

            $tables = [
                'project_user',
                'project_tag',
                'project_todo',
                'project_payments',
                'project_chat_rooms',
                'project_resources',
                'project_stakeholders',
                'project_risk_assessments',
                'project_quality_metrics',
                'project_change_requests',
                'project_milestones',
                'project_dependencies',
                'project_budgets',
                'project_time_logs',
                'project_reports',
                'project_issues',
                'project_documents',
                'project_integrations',
                'project_feedback',
                'project_workflows',
                'project_sprints',
                'project_team_members',
                'project_team_roles',
                'project_cost_centers',
                'project_expenses',
                'project_risks',
                'project_audits',


            ];

            foreach ($tables as $table) {
                DB::table($table)
                    ->whereIn('project_id', $nonArchivedProjectIds)
                    ->delete();
            }

            DB::table('projects')
                ->whereIn('id', $nonArchivedProjectIds)
                ->delete();
        });
    }


    public function searchProjects(string $searchTerm, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        $query = DB::table('projects')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            })->select(
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.description',
                'projects.status',
                'projects.project_code'
            );

        return $query->paginate($perPage, ['*'], 'page', $page);
    }



    public function archiveProject(int $projectId): void
    {
        DB::transaction(function () use ($projectId): void {
            $projectId = (int) $projectId;

            $projectExists = DB::table('projects')->where('id', $projectId)->exists();

            if (!$projectExists) {
                throw new Exception("Project with ID {$projectId} does not exist.");
            }

            DB::table('projects')
                ->where('id', $projectId)
                ->update(['is_archived' => true]);
        });
    }

    public function restoreProjectById(int $projectId): void
    {

        $projectRestored = DB::table('projects')
            ->where('id', $projectId)
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null, 'updated_at' => now()]);

        if ($projectRestored === 0) {
            throw new Exception("Project with ID {$projectId} does not exist or is not soft-deleted.");
        }
    }



    function getProjectData()
    {
        return Cache::flexible("project_data_for_user_project", [60, 120], function () {
            return DB::transaction(function () {

                $categories = DB::table('project_categories')
                    ->select('id', 'name')
                    ->where('created_by', 1) // Add user condition
                    ->get();

                // Fetch project phases
                $phases = DB::table('project_phases')
                    ->select('id', 'name')
                    ->get();

                // Fetch project priorities
                $priorities = DB::table('project_priorities')
                    ->select('id', 'name')
                    ->get();

                // Fetch project types
                $types = DB::table('project_types')
                    ->select('id', 'name')
                    ->get();

                // Fetch project statuses
                $statuses = DB::table('project_statuses')
                    ->select('id', 'name')
                    ->get();

                // Grouping data into a structured format
                return [
                    'categories' => $categories,
                    'phases' => $phases,
                    'priorities' => $priorities,
                    'types' => $types,
                    'statuses' => $statuses,
                ];
            });
        });
    }

    function getProjectDataForUser(int $userId)
    {

        $cacheKey = "project_data_for_user_{$userId}";

        if ($userId === 1) {
            throw new InvalidArgumentException('User ID must not be 1.');
        }

        return Cache::flexible($cacheKey, [60, 120], function () use ($userId) {
            return DB::transaction(function () use ($userId) {

                $categories = DB::table('project_categories')
                    ->select('id', 'name')
                    ->where('created_by', $userId)
                    ->get();


                $phases = DB::table('project_phases')
                    ->select('id', 'name')
                    ->where('created_by', $userId)
                    ->get();


                $priorities = DB::table('project_priorities')
                    ->select('id', 'name')
                    ->where('created_by', $userId)
                    ->get();


                $types = DB::table('project_types')
                    ->select('id', 'name')
                    ->where('created_by', $userId)
                    ->get();


                $statuses = DB::table('project_statuses')
                    ->select('id', 'name')
                    ->where('created_by', $userId)
                    ->get();


                return [
                    'categories' => $categories,
                    'phases' => $phases,
                    'priorities' => $priorities,
                    'types' => $types,
                    'statuses' => $statuses,
                ];
            });
        });
    }




}
