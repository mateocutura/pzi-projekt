<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $projects = Project::with(['mentor', 'submissions'])
            ->where('student_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->progress_percentage = $project->progressPercentage;
                return $project;
            });

        return response()->json($projects);
    }


    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'vision'      => 'required|string',
            'github_url'  => 'nullable|url|max:255',
        ]);

        $project = Project::create([
            ...$data,
            'student_id' => $request->user()->id,
            'status'     => 'pending',
        ]);

        return response()->json($project, 201);
    }


    public function show(Request $request, Project $project): JsonResponse
    {
        if ($project->student_id !== $request->user()->id) {
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

        $project->load('mentor');
        $project->progress_percentage = $project->progressPercentage;

        return response()->json([
            'project' => $project,
            'phases'  => $phases,
        ]);
    }


    public function update(Request $request, Project $project): JsonResponse
    {
        if ($project->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        if ($project->status !== 'pending') {
            return response()->json(['message' => 'Projekt više nije u stanju pending, ne može se mijenjati.'], 422);
        }

        $data = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'vision'      => 'sometimes|string',
            'github_url'  => 'nullable|url|max:255',
        ]);

        $project->update($data);

        return response()->json($project);
    }
}
