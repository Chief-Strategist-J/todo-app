<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTodosToProjectsRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;

use function App\Helper\errorMsg;
use function App\Helper\getIndianTime;
use function App\Helper\successMessage;

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


class ProjectController extends Controller
{

    protected $project;


    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    private function prepareProjectData(array $validated): array
    {
        $uuid = (string) Str::uuid();
        $slug = Str::slug($validated['name']);
        $project_code = 'PRJ-' . strtoupper(Str::random(6));
        $start_date = Carbon::today();



        return [
            'uuid' => $uuid,
            'name' => $validated['name'], // No encryption
            'slug' => Crypt::encryptString($slug),
            'description' => $validated['description'] ?? '', // No encryption
            'status' => Crypt::encryptString($validated['status'] ?? 'pending'),
            'is_public' => (bool) ($validated['is_public'] ?? false),
            'project_code' => Crypt::encryptString($project_code),
            'created_by' => (int) $validated['created_by'],
            'updated_by' => isset($validated['updated_by']) ? (int) $validated['updated_by'] : null,
            'start_date' => $start_date->toDateString(),
            'end_date' => isset($validated['end_date']) ? Carbon::parse($validated['end_date'])->toDateString() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }


    private function storeProject(array $data): int
    {
        return $this->project->createProject($data);
    }

    private function assignCategory($projectId, array $categoryNames)
    {
        return $this->project->assignCategoriesToProject($projectId, $categoryNames);
    }

    private function assignPhase($projectId, array $phaseNames)
    {
        return $this->project->assignPhasesToProject($projectId, $phaseNames);
    }

    private function assignStatus($projectId, array $statusNames)
    {
        return $this->project->assignStatusesToProject($projectId, $statusNames);
    }

    private function assignPriorities($projectId, array $priorityNames)
    {
        return $this->project->assignPrioritiesToProject($projectId, $priorityNames);
    }

    private function assignTypes($projectId, array $typeNames)
    {
        return $this->project->assignTypesToProject($projectId, $typeNames);
    }


    private function decryptProjectData(LengthAwarePaginator $projects): LengthAwarePaginator
    {
        // Decrypt each project in the collection
        $projects->getCollection()->transform(function ($project) {
            $project->slug = Crypt::decryptString($project->slug);
            $project->status = Crypt::decryptString($project->status);
            $project->project_code = Crypt::decryptString($project->project_code);
            return $project;
        });

        return $projects;
    }


    private function prepareAndEncryptData(array $data): array
    {
        $encryptable = ['slug', 'status', 'project_code'];

        foreach ($encryptable as $field) {
            if (isset($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }

        $data['updated_at'] = now();

        return $data;
    }

    /// create project
    public function createProject(StoreProjectRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $data = $this->prepareProjectData($validated);

            $projectId = $this->storeProject($data);

            $this->assignCategory(
                projectId: $projectId,
                categoryNames: [$validated['project_category_name']]
            );

            $this->assignPhase(
                projectId: $projectId,
                phaseNames: [$validated['project_phase_name']]
            );

            $this->assignStatus(
                projectId: $projectId,
                statusNames: [$validated['project_status_name']]
            );

            $this->assignPriorities(
                projectId: $projectId,
                priorityNames: [$validated['project_priority_name']]
            );

            $this->assignTypes(
                projectId: $projectId,
                typeNames: [$validated['project_priority_name']]
            );

            return successMessage(message: 'Assignments successfully processed.');
        } catch (Exception $e) {
            return errorMsg(message: 'An error occurred while processing the assignments.', data: $e->getMessage(), statusCode: 500);
        }
    }

    /// get projects
    public function getProjects(Request $request): LengthAwarePaginator
    {
        $creatorId = $request->input('creator_id');
        $cacheKey = "projects_by_creator_{$creatorId}";
        $project = $this->project;

        return Cache::flexible($cacheKey, [3600, 7200], function () use ($creatorId, $project) {
            $projects = $project->fetchActiveProjects($creatorId);
            return $this->decryptProjectData($projects);
        });
    }

    // update the projects
    public function updateProject(UpdateProjectRequest $request, int $projectId)
    {
        $validated = $request->validated();
        $projectId = $request->input('project_id');
        $updateData = $this->prepareAndEncryptData($validated);

        $this->project->updateProject($updateData, $projectId);

        return successMessage(message: 'Project updated successfully.');
    }

    public function assignTodosToProjects(AssignTodosToProjectsRequest $request): JsonResponse
    {
        try {
            $this->project->assignTodoToProjects($request->input('assignments'));
            return successMessage('Assignments successfully processed.');
        } catch (Exception $e) {
            return errorMsg('An error occurred while processing the assignments.', data: $e->getMessage(), statusCode: 500);
        }
    }


    public function getPaginatedProjectsForTodo(Request $request): JsonResponse
    {
        $todoId = $request->input('todo_id');
        $userId = $request->input('user_id');
        $perPage = 20;

        $paginatedProjects = $this->project->getPaginatedProjectsForTodo(
            $todoId,
            $userId,
            $request->input('page', 1),
            $perPage
        );

        // Decrypt project data
        $paginatedProjects = $this->decryptProjectData($paginatedProjects);

        return successMessage('Projects retrieved successfully', true, $paginatedProjects);
    }


    public function getPaginatedTodosForProject(Request $request): JsonResponse
    {


        $projectId = (int) $request->input('project_id');
        $userId = (int) $request->input('user_id');
        $page = (int) $request->input('page', 1);
        $perPage = 20;

        $paginatedTodos = $this->project->getPaginatedTodosForProject(
            $projectId,
            $userId,
            $page,
            $perPage
        );

        return successMessage('Todos retrieved successfully.', true, $paginatedTodos);
    }

    public function deleteProject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return errorMsg('Validation errors', 422, $validator->errors());
        }

        $projectId = (int) $request->input('project_id');

        try {
            $this->project->deleteProjectAndAssociates($projectId);
            return successMessage('Project and its associated records have been successfully deleted.');
        } catch (Exception $e) {
            return errorMsg($e->getMessage(), 500);
        }
    }

    public function bulkDeleteProjects(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_ids' => 'required|array',
            'project_ids.*' => 'integer|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return errorMsg('Validation errors', 422, $validator->errors());
        }

        $projectIds = $request->input('project_ids');

        try {
            $this->project->bulkDeleteProjects($projectIds);
            return successMessage('Selected projects and their associated records have been successfully deleted.');
        } catch (Exception $e) {
            return errorMsg($e->getMessage(), 500);
        }
    }

    public function searchProjects(Request $request): JsonResponse
    {
        $searchTerm = $request->input('search');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('perPage', 20);

        if (!$searchTerm) {
            return errorMsg('Search term is required');
        }

        $paginatedProjects = $this->project->searchProjects($searchTerm, $page, $perPage);

        // Decrypt project data
        $paginatedProjects = $this->decryptProjectData($paginatedProjects);

        return successMessage('Projects retrieved successfully', true, $paginatedProjects);
    }


    public function archiveProject(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return errorMsg('Validation errors', 422, $validator->errors());
        }

        $projectId = $request->input('project_id');

        try {
            $this->project->archiveProject($projectId);
            return successMessage('Project has been successfully archived.');
        } catch (Exception $e) {
            return errorMsg($e->getMessage(), 500);
        }
    }

    public function restoreProject(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        $projectId = (int) $request->input('project_id');

        try {
            $this->project->restoreProjectById($projectId);
            return successMessage("Project with ID {$projectId} has been restored.");
        } catch (Exception $e) {
            return errorMsg($e->getMessage());
        }
    }

    public function getProjectCategoryDetail(Request $request): JsonResponse
    {
        try {
            return successMessage("get list of project category data.", data: $this->project->getProjectData());
        } catch (Exception $e) {
            return errorMsg($e->getMessage());
        }
    }

    public function getProjectForUserCategoryDetail(Request $request): JsonResponse
    {

        $request->validate([
            'user_id' => 'required|integer|not_in:1'
        ]);

        $userId = $request->input('user_id');

        try {
            return successMessage("Get list of project category data.", data: $this->project->getProjectDataForUser($userId));
        } catch (Exception $e) {
            return errorMsg($e->getMessage());
        }
    }

}
