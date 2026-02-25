<?php
namespace App\Imports;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EditorTaskImport implements ToCollection
{
    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $key => $row) {

            if ($key == 0) continue; // Skip header row

            $employeeName = $row[0]; // Adjust column index if needed
            $project = $row[1];
            $module = $row[2];
            $platform = $row[3];
            $taskName = $row[4];
            $estimateDate = $row[5];
            $startDate = $row[6];
            $workDate = $row[7];
            $estimateTime = $row[8];
            $actualTime = $row[9];
            $taskStatus = $row[10];
            $timerStatus = $row[11];

            if (!$employeeName || !$taskName) continue;

            $employee = Employee::firstOrCreate([
                'name' => $employeeName
            ]);

            $estimatedMinutes = $this->convertToMinutes($estimateTime);
            $actualMinutes = $this->convertToMinutes($actualTime);

            $variance = $actualMinutes - $estimatedMinutes;

            $efficiency = $actualMinutes > 0
                ? ($estimatedMinutes / $actualMinutes) * 100
                : 0;

            $category = 'ontime';

            if ($actualMinutes > $estimatedMinutes) {
                $category = 'overdue';
            } elseif ($actualMinutes < $estimatedMinutes) {
                $category = 'underdue';
            }

            Task::create([
                'employee_id' => $employee->id,
                'upload_id' => $this->uploadId,
                'project' => $project,
                'module' => $module,
                'platform' => $platform,
                'task_name' => $taskName,
                'estimate_date' => $estimateDate,
                'start_date' => $startDate,
                'work_date' => $workDate,
                'estimated_minutes' => $estimatedMinutes,
                'actual_minutes' => $actualMinutes,
                'variance_minutes' => $variance,
                'efficiency_percent' => round($efficiency, 2),
                'time_category' => $category,
                'task_status' => $taskStatus,
                'timer_status' => $timerStatus,
            ]);
        }
    }

    private function convertToMinutes($time)
    {
        if (!$time) return 0;

        $parts = explode(':', $time);

        return ($parts[0] * 60) + $parts[1];
    }
}