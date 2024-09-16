<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
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
use Illuminate\Validation\ValidationException;


class ProjectController extends Controller
{

    private function prepareProjectData(array $validated): array
    {
        $uuid = (string) Str::uuid();
        $slug = Str::slug($validated['name']);
        $project_code = 'PRJ-' . strtoupper(Str::random(6));
        $start_date = Carbon::today();

        return [
            'uuid' => $uuid,
            'name' => Crypt::encryptString($validated['name']),
            'slug' => Crypt::encryptString($slug),
            'description' => Crypt::encryptString($validated['description'] ?? ''),
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

    private function storeProject(array $data): void
    {
        $project = new Project();
        $project->createProject($data);
    }

    private function decryptProjectData(LengthAwarePaginator $projects): LengthAwarePaginator
    {
        $projects->getCollection()->transform(function ($project) {
            $project->name = Crypt::decryptString($project->name);
            $project->slug = Crypt::decryptString($project->slug);
            $project->description = Crypt::decryptString($project->description);
            $project->status = Crypt::decryptString($project->status);
            return $project;
        });
    
        return $projects;
    }    

    private function prepareAndEncryptData(array $data): array
    {
        $encryptable = ['name', 'slug', 'description', 'status', 'project_code'];

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
            $this->storeProject($data);

            return response()->json(['message' => 'Project created successfully.']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error creating project', 'error' => $e->getMessage()], 500);
        }
    }

    /// get projects
    public function getProjects(Request $request): LengthAwarePaginator
    {
        $creatorId = $request->input('creator_id');
        $cacheKey = "projects_by_creator_{$creatorId}";
        $project = new Project();

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

        $project = new Project();
        $project->updateProject($updateData, $projectId);

        return response()->json(['message' => 'Project updated successfully.']);
    }

    

}
