<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ProjectSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{

    public function store(Request $request, Project $project): JsonResponse
    {

        if ($project->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }


        if ($project->status !== 'accepted') {
            return response()->json(['message' => 'Projekt mora biti prihvaćen prije predaje faza.'], 422);
        }

        $data = $request->validate([
            'phase_id'        => 'required|exists:project_phases,id',
            'submission_text' => 'required|string',
        ]);


        $existing = ProjectSubmission::where('project_id', $project->id)
            ->where('phase_id', $data['phase_id'])
            ->first();

        if ($existing && in_array($existing->status, ['approved'])) {
            return response()->json(['message' => 'Ova faza je već odobrena.'], 422);
        }


        $submission = ProjectSubmission::updateOrCreate(
            [
                'project_id' => $project->id,
                'phase_id'   => $data['phase_id'],
            ],
            [
                'status'          => 'submitted',
                'submission_text' => $data['submission_text'],
                'submitted_at'    => now(),
                'mentor_feedback' => null,
            ]
        );

        return response()->json($submission, 201);
    }


    public function update(Request $request, Project $project, ProjectSubmission $submission): JsonResponse
    {
        if ($project->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Nije pronađeno.'], 404);
        }

        if ($submission->status === 'approved') {
            return response()->json(['message' => 'Odobrena faza ne može se mijenjati.'], 422);
        }

        $data = $request->validate([
            'submission_text' => 'required|string',
        ]);

        $submission->update([
            'submission_text' => $data['submission_text'],
            'status'          => 'submitted',
            'submitted_at'    => now(),
        ]);

        return response()->json($submission);
    }
}
