<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    public function __construct(private readonly ProjectService $projects) {}

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('milestones', $project);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->projects->addMilestone($project, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Milestone ditambah.');
    }

    public function update(Request $request, Project $project, ProjectMilestone $milestone): RedirectResponse
    {
        $this->authorize('milestones', $project);
        abort_unless($milestone->project_id === $project->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,in_progress,completed,delayed'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->projects->updateMilestone($milestone, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Milestone dikemas kini.');
    }
}
