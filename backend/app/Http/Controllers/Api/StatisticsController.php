<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectSubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();


        if ($user->isAdmin()) {
            $projectQuery = Project::query();
        } else {
            $projectQuery = Project::where('mentor_id', $user->id);
        }

        $totalProjects  = (clone $projectQuery)->count();
        $pendingCount   = (clone $projectQuery)->where('status', 'pending')->count();
        $acceptedCount  = (clone $projectQuery)->where('status', 'accepted')->count();
        $rejectedCount  = (clone $projectQuery)->where('status', 'rejected')->count();


        $completedCount = (clone $projectQuery)
            ->whereHas('submissions', function ($q) {
                $q->where('status', 'approved');
            }, '>=', 10)
            ->count();


        $studentsWithProjects = User::where('role', 'student')
            ->whereHas('projects')
            ->count();


        $progressDistribution = [];
        for ($i = 0; $i <= 10; $i++) {
            $count = (clone $projectQuery)
                ->withCount(['submissions as approved_count' => function ($q) {
                    $q->where('status', 'approved');
                }])
                ->having('approved_count', $i)
                ->count();

            if ($count > 0) {
                $progressDistribution[] = [
                    'approved_phases' => $i,
                    'count'           => $count,
                ];
            }
        }

        return response()->json([
            'total_projects'         => $totalProjects,
            'pending_count'          => $pendingCount,
            'accepted_count'         => $acceptedCount,
            'rejected_count'         => $rejectedCount,
            'completed_count'        => $completedCount,
            'students_with_projects' => $studentsWithProjects,
            'progress_distribution'  => $progressDistribution,
        ]);
    }
}
