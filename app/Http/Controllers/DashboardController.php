<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $verticals = Task::select('vertical')->distinct()->pluck('vertical');
        $teams = Task::select('team')->distinct()->pluck('team');
        $employees = Employee::select('id','name')->get();

        return view('dashboard', compact('verticals','teams','employees'));
    }

   public function getDashboardData(Request $request)
    {
        $query = Task::query();

        // Filters
        if ($request->vertical) {
            $query->where('vertical', $request->vertical);
        }

        if ($request->team) {
            $query->where('team', $request->team);
        }

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->start_from && $request->start_to) {
            $query->whereBetween('start_date', [
                $request->start_from,
                $request->start_to
            ]);
        }

        // Clone for reuse in grouped queries
        $baseQuery = clone $query;

        $tasks = $baseQuery->get();

        $totalTasks = $tasks->count();
        $totalEmployees = $tasks->pluck('employee_id')->unique()->count();

        $totalEstimatedMinutes = $tasks->sum('estimated_minutes');
        $totalActualMinutes = $tasks->sum('actual_minutes');

        $totalEstimatedHours = round($totalEstimatedMinutes / 60, 2);
        $totalActualHours = round($totalActualMinutes / 60, 2);

        $varianceHours = round(($totalActualMinutes - $totalEstimatedMinutes) / 60, 2);

        $accuracy = $totalActualMinutes > 0
            ? round(($totalEstimatedMinutes / $totalActualMinutes) * 100, 2)
            : 0;

        // Time Category
        $overdue = $tasks->where('actual_minutes', '>', 'estimated_minutes')->count();
        $ontime = $tasks->where('actual_minutes', '=', 'estimated_minutes')->count();
        $underdue = $tasks->where('actual_minutes', '<', 'estimated_minutes')->count();

        // Performance Split (FOR YOUR DOUGHNUT CHART)
        $performanceSplit = [
            'overrun' => $overdue,
            'ontime'  => $ontime + $underdue // treat underdue as on-time performance
        ];

        // Task Status
        $taskStatus = $tasks->groupBy('task_status')->map->count()->toArray();

        // Monthly Trend
        $monthlyTrend = (clone $query)
            ->selectRaw("
                DATE_FORMAT(start_date, '%Y-%m') as month,
                SUM(estimated_minutes) as estimated,
                SUM(actual_minutes) as actual
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Employee Leaderboard
        $employeeLeaderboard = (clone $query)
            ->join('employees','tasks.employee_id','=','employees.id')
            ->selectRaw("
                employees.name,
                SUM(estimated_minutes) as total_estimated,
                SUM(actual_minutes) as total_actual,
                ROUND((SUM(estimated_minutes)/NULLIF(SUM(actual_minutes),0))*100,2) as efficiency
            ")
            ->groupBy('employees.name')
            ->orderByDesc('efficiency')
            ->limit(10)
            ->get();

        // Team Ranking
        $teamRanking = (clone $query)
            ->selectRaw("
                team,
                SUM(estimated_minutes) as total_estimated,
                SUM(actual_minutes) as total_actual,
                ROUND((SUM(estimated_minutes)/NULLIF(SUM(actual_minutes),0))*100,2) as efficiency
            ")
            ->groupBy('team')
            ->orderByDesc('efficiency')
            ->get();

        return response()->json([
            'totalTasks' => $totalTasks,
            'totalEmployees' => $totalEmployees,
            'totalEstimatedHours' => $totalEstimatedHours,
            'totalActualHours' => $totalActualHours,
            'totalVarianceHours' => $varianceHours,
            'accuracy' => $accuracy,
            'timeCategory' => [
                'overdue' => $overdue,
                'ontime' => $ontime,
                'underdue' => $underdue,
            ],
            'performanceSplit' => $performanceSplit,
            'taskStatus' => $taskStatus,
            'monthlyTrend' => $monthlyTrend,
            'employeeLeaderboard' => $employeeLeaderboard,
            'teamRanking' => $teamRanking,
        ]);
    }
}