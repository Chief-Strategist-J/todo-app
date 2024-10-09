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
        DB::transaction(function () use ($data) {
            DB::table('projects')->insert($data);
        });
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




}
