<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'upload_id', 'vertical', 'team', 'project', 'billing_type',
        'module', 'platform', 'external_task_id', 'task_name',
        'estimate_date', 'start_date', 
        'estimated_minutes', 'actual_minutes', 'variance_minutes', 'efficiency_percent',
        'time_category', 'timer_status', 'task_status', 'reason', 'report_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}