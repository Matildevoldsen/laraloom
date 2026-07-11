<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->string('q')->trim()->limit(80)->toString();
        $projects = Project::query()
            ->where('status', ProjectStatus::Published)
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('name', "%{$search}%")
                        ->orWhereLike('tagline', "%{$search}%")
                        ->orWhereLike('description', "%{$search}%");
                });
            })
            ->latest('featured_at')
            ->latest('published_at')
            ->latest('id')
            ->cursorPaginate(20);

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function show(Project $project): ProjectResource
    {
        abort_unless($project->status === ProjectStatus::Published, 404);

        return ProjectResource::make($project->load('user'));
    }
}
