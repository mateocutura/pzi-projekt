<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $projects = Project::with(['student', 'submissions'])
            ->where('mentor_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->progress_percentage = $project->progressPercentage;
                return $project;
            });


        if ($projects->isEmpty()) {
            $unassigned = Project::with(['student'])
                ->whereNull('mentor_id')
                ->where('status', 'pending')
                ->latest()
                ->get();
            return response()->json([
                'my_projects'       => $projects,
                'unassigned_projects' => $unassigned,
            ]);
        }

        return response()->json([
            'my_projects'         => $projects,
            'unassigned_projects' => [],
        ]);
    }


    public function show(Request $request, Project $project): JsonResponse
    {
        if ($project->mentor_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        $phases = ProjectPhase::orderBy('step_number')->get()->map(function ($phase) use ($project) {
            $submission = ProjectSubmission::where('project_id', $project->id)
                ->where('phase_id', $phase->id)
                ->first();

            return [
                'phase'      => $phase,
                'submission' => $submission,
            ];
        });

        $project->load('student');
        $project->progress_percentage = $project->progressPercentage;

        return response()->json([
            'project' => $project,
            'phases'  => $phases,
        ]);
    }


    public function updateStatus(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'status'           => 'required|in:accepted,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);


        if ($project->mentor_id && $project->mentor_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        $project->update([
            'status'           => $data['status'],
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'mentor_id'        => $request->user()->id, 
        ]);

        return response()->json($project->fresh(['student']));
    }


    public function reviewSubmission(Request $request, Project $project, ProjectSubmission $submission): JsonResponse
    {
        if ($project->mentor_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        if ($submission->project_id !== $project->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        $data = $request->validate([
            'status'          => 'required|in:approved,rejected',
            'mentor_feedback' => 'nullable|string',
        ]);

        $submission->update([
            'status'          => $data['status'],
            'mentor_feedback' => $data['mentor_feedback'] ?? null,
            'approved_at'     => $data['status'] === 'approved' ? now() : null,
        ]);

        return response()->json($submission->fresh(['phase']));
    }


    public function gradeProject(Request $request, Project $project): JsonResponse
    {
        if ($project->mentor_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        $data = $request->validate([
            'grade' => 'required|integer|min:1|max:5',
        ]);

        $project->update(['grade' => $data['grade']]);

        return response()->json($project->fresh(['student']));
    }
}
