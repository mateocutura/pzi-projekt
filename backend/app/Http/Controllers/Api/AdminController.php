<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function listUsers(): JsonResponse
    {
        $users = User::withCount(['projects', 'mentorProjects'])
            ->latest()
            ->get();

        return response()->json($users);
    }


    public function updateUser(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => 'required|in:student,mentor,admin',
            'name' => 'sometimes|string|max:255',
        ]);

        $user->update($data);

        return response()->json($user->fresh());
    }


    public function assignMentor(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'mentor_id' => 'required|exists:users,id',
        ]);


        $mentor = User::findOrFail($data['mentor_id']);
        if (! $mentor->isMentor()) {
            return response()->json(['message' => 'Odabrani korisnik nije mentor.'], 422);
        }

        $project->update(['mentor_id' => $mentor->id]);

        return response()->json($project->fresh(['student', 'mentor']));
    }


    public function listProjects(): JsonResponse
    {
        $projects = Project::with(['student', 'mentor'])
            ->latest()
            ->get()
            ->map(function ($project) {
                $project->progress_percentage = $project->progressPercentage;
                return $project;
            });

        return response()->json($projects);
    }
}
