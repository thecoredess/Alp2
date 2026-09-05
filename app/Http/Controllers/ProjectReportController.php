<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\Project\ProjectException;
use App\Services\Project\ProjectReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectReportController extends Controller
{
    public function __construct(private readonly ProjectReportService $reports) {}

    public function edit(Project $project): View
    {
        $this->authorize('report', $project);
        $project->load('report');

        return view('projects.report', ['project' => $project, 'report' => $project->report]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('report', $project);

        $validated = $request->validate([
            'summary' => ['nullable', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'beneficiary_count' => ['nullable', 'integer', 'min:0'],
            'impact_summary' => ['nullable', 'string', 'max:5000'],
            'completion_summary' => ['nullable', 'string', 'max:5000'],
            'issues' => ['nullable', 'string', 'max:5000'],
            'lessons_learned' => ['nullable', 'string', 'max:5000'],
            'final_remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $this->reports->submit($project, $request->user(), $validated);
        } catch (ProjectException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('projects.show', $project)->with('status', 'Laporan akhir dihantar.');
    }
}
