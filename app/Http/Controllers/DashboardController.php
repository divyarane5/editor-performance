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
        $baseQuery = Task::query();

        // FILTERS
        if ($request->vertical)
            $baseQuery->where('vertical', $request->vertical);

        if ($request->team)
            $baseQuery->where('team', $request->team);

        if ($request->employee_id)
            $baseQuery->where('employee_id', $request->employee_id);

        if ($request->start_from && $request->start_to)
            $baseQuery->whereBetween('start_date', [$request->start_from, $request->start_to]);

        // CLONE QUERY FOR MULTIPLE CALCULATIONS
        $summary = (clone $baseQuery)->selectRaw("
            COUNT(*) as total_tasks,
            COUNT(DISTINCT employee_id) as total_employees,
            SUM(estimated_minutes) as total_estimated,
            SUM(actual_minutes) as total_actual,
            SUM(variance_minutes) as total_variance
        ")->first();

        $timeCategory = (clone $baseQuery)
            ->select('time_category', DB::raw('COUNT(*) as total'))
            ->groupBy('time_category')
            ->pluck('total','time_category');

        $taskStatus = (clone $baseQuery)
            ->select('task_status', DB::raw('COUNT(*) as total'))
            ->groupBy('task_status')
            ->pluck('total','task_status');

        return response()->json([
            'totalTasks' => $summary->total_tasks ?? 0,
            'totalEmployees' => $summary->total_employees ?? 0,
            'totalEstimatedHours' => round(($summary->total_estimated ?? 0)/60,2),
            'totalActualHours' => round(($summary->total_actual ?? 0)/60,2),
            'totalVarianceHours' => round(($summary->total_variance ?? 0)/60,2),
            'timeCategory' => [
                'overdue' => $timeCategory['overdue'] ?? 0,
                'ontime' => $timeCategory['ontime'] ?? 0,
                'underdue' => $timeCategory['underdue'] ?? 0,
            ],
            'taskStatus' => $taskStatus,
        ]);
    }
}