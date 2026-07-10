<?php

namespace App\Http\Controllers;

use App\Actions\CreateProjectAction;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\ProjectStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): View
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
            ->paginate(18)
            ->withQueryString();

        return view('projects.index', compact('projects', 'search'));
    }

    public function show(Project $project): View
    {
        abort_unless($project->status === ProjectStatus::Published, 404);
        $project->load('user');

        return view('projects.show', compact('project'));
    }

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('projects.create');
    }

    public function store(StoreProjectRequest $request, CreateProjectAction $createProject): RedirectResponse
    {
        Gate::authorize('create', Project::class);
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $project = $createProject->execute($user, $request->validated());

        return to_route('projects.show', $project)->with('status', 'Project published.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);
        $project->delete();

        return to_route('projects.index')->with('status', 'Project deleted.');
    }
}
