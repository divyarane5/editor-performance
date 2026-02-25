<?php

namespace App\Imports;

use App\Models\Task;
use App\Models\Employee;
use App\Models\Upload;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TasksImport implements ToModel, WithHeadingRow
{
    protected $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function model(array $row)
    {
        // 1️⃣ Employee
        $employee = Employee::firstOrCreate([
            'name' => $row['employee'] ?? 'Unknown',
        ]);

        // 2️⃣ Times in minutes
        $estimatedMinutes = $this->timeToMinutes($row['estimate_time'] ?? null);
        $actualMinutes    = $this->timeToMinutes($row['task_work_time'] ?? null);

        // Variance & efficiency
        $variance = $actualMinutes - $estimatedMinutes;

        $efficiency = $estimatedMinutes 
            ? round(($estimatedMinutes / max($actualMinutes, 1)) * 100, 2) 
            : 0;

        if ($variance > 0) {
            $timeCategory = 'overdue';
        } elseif ($variance < 0) {
            $timeCategory = 'underdue';
        } else {
            $timeCategory = 'ontime';
        }

        // 3️⃣ Dates
        $estimateDate = $this->parseDate($row['estimate_date'] ?? null);
        $startDate    = $this->parseDate($row['task_start_date'] ?? null);

        // 4️⃣ Create Task
        return new Task([
            'employee_id'      => $employee->id,
            'upload_id'        => $this->upload->id,
            'vertical'         => $row['vertical'] ?? null,
            'team'             => $row['team'] ?? null,
            'project'          => $row['project'] ?? null,
            'billing_type'     => $row['billing_type'] ?? null,
            'module'           => $row['module'] ?? null,
            'platform'         => $row['platform'] ?? null,
            'external_task_id' => $row['task_id'] ?? null,
            'task_name'        => $row['task'] ?? null,
            'estimate_date'    => $estimateDate,
            'start_date'       => $startDate,
            'estimated_minutes'=> $estimatedMinutes,
            'actual_minutes'   => $actualMinutes,
            'variance_minutes' => $variance,
            'efficiency_percent'=> $efficiency,
            'time_category'    => $timeCategory,
            'timer_status'     => $row['timer_status'] ?? null,
            'task_status'      => $row['task_status'] ?? null,
            'reason'           => $row['reason'] ?? null,
        ]);
    }

    /**
     * Convert HH:MM:SS to total minutes
     */
    private function timeToMinutes($time)
    {
        if (!$time) return 0;

        // If Excel decimal time (like 0.0833)
        if (is_numeric($time)) {
            return round($time * 24 * 60);
        }

        // If string 02:13:01
        $parts = explode(':', $time);

        if (count($parts) === 3) {
            return ($parts[0] * 60) + $parts[1];
        }

        return 0;
    }

    /**
     * Parse various date formats safely
     */
    private function parseDate($value)
    {
        if (!$value) return null;

        // If already DateTime
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        // If numeric (Excel serial number)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                ->format('Y-m-d');
        }

        // If string like 02-02-2026
        try {
            return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}