@extends('layouts.app')

@section('content')
<style>
    /* General */
.dashboard-card {
    border-radius: 10px;
}

/* KPI Cards */
.kpi-card {
    border-radius: 10px;
    transition: all 0.2s ease-in-out;
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

.kpi-card h5 {
    font-size: 1.4rem;
}

.card-header {
    border-bottom: 1px solid #f1f1f1;
}

/* Tables */
.table th {
    font-weight: 600;
    font-size: 0.85rem;
}

.table td {
    font-size: 0.85rem;
}

/* Loader */
#loader {
    min-height: 60px;
}
</style>
<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Performance Dashboard</h4>
    </div>

    {{-- FILTERS --}}
    <div class="card shadow-sm mb-4 dashboard-card">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Vertical</label>
                    <select id="verticalFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($verticals as $v)
                            <option value="{{ $v }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Team</label>
                    <select id="teamFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($teams as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Employee</label>
                    <select id="employeeFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Date Range</label>
                    <div class="d-flex gap-2">
                        <input type="date" id="startFrom" class="form-control">
                        <input type="date" id="startTo" class="form-control">
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- LOADER --}}
    <div id="loader" class="text-center my-4 d-none">
        <div class="spinner-border text-primary"></div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-3 mb-4">

        @php
            $kpis = [
                ['id'=>'totalTasks','label'=>'Total Tasks','class'=>'primary'],
                ['id'=>'totalEmployees','label'=>'Total Employees','class'=>'success'],
                ['id'=>'totalEstimated','label'=>'Estimated Hours','class'=>'info'],
                ['id'=>'totalActual','label'=>'Actual Hours','class'=>'warning'],
                ['id'=>'totalVariance','label'=>'Variance Hours','class'=>'secondary','boxId'=>'varianceBox'],
                ['id'=>'accuracy','label'=>'Estimation Accuracy','class'=>'dark']
            ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card kpi-card border-0 shadow-sm bg-{{ $kpi['class'] }} text-white"
                 id="{{ $kpi['boxId'] ?? '' }}">
                <div class="card-body">
                    <h5 id="{{ $kpi['id'] }}" class="fw-bold mb-1">0</h5>
                    <small class="text-light">{{ $kpi['label'] }}</small>
                </div>
            </div>
        </div>
        @endforeach

    </div>

    {{-- CHARTS --}}
    <div class="row g-4 mb-4">

        <div class="col-lg-4">
            <div class="card shadow-sm dashboard-card">
                <div class="card-header bg-white fw-semibold">
                    Time Category Distribution
                </div>
                <div class="card-body">
                    <canvas id="timeCategoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm dashboard-card">
                <div class="card-header bg-white fw-semibold">
                    Task Status Distribution
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm dashboard-card">
                <div class="card-header bg-white fw-semibold">
                    Overrun vs On-Time
                </div>
                <div class="card-body">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- MONTHLY TREND --}}
    <div class="card shadow-sm dashboard-card mb-4">
        <div class="card-header bg-white fw-semibold">
            Monthly Estimated vs Actual Trend
        </div>
        <div class="card-body">
            <canvas id="monthlyTrendChart"></canvas>
        </div>
    </div>

    {{-- TABLES --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm dashboard-card">
                <div class="card-header bg-white fw-semibold">
                    Top 10 Employee Efficiency
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Est Hrs</th>
                                <th>Act Hrs</th>
                                <th>Efficiency %</th>
                            </tr>
                        </thead>
                        <tbody id="employeeLeaderboardBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm dashboard-card">
                <div class="card-header bg-white fw-semibold">
                    Team Efficiency Ranking
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Team</th>
                                <th>Est Hrs</th>
                                <th>Act Hrs</th>
                                <th>Efficiency %</th>
                            </tr>
                        </thead>
                        <tbody id="teamRankingBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('scripts')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function(){

    let timeChart = null;
    let statusChart = null;
    let performanceChart = null;
    let monthlyTrendChart = null;

    function safeNumber(value){
        return parseFloat(value) || 0;
    }

    function destroyChart(chart){
        if(chart){
            chart.destroy();
        }
    }

    function loadDashboard(){

        $.ajax({
            url: "{{ url('dashboard/data') }}",
            type: "GET",
            data: {
                vertical: $('#verticalFilter').val(),
                team: $('#teamFilter').val(),
                employee_id: $('#employeeFilter').val(),
                start_from: $('#startFrom').val(),
                start_to: $('#startTo').val()
            },
            beforeSend: function(){
                $('#loader').removeClass('d-none');
            },
            success: function(data){

                // ================= KPI =================
                $('#totalTasks').text(data.totalTasks ?? 0);
                $('#totalEmployees').text(data.totalEmployees ?? 0);
                $('#totalEstimated').text(safeNumber(data.totalEstimatedHours) + ' Hrs');
                $('#totalActual').text(safeNumber(data.totalActualHours) + ' Hrs');

                let variance = safeNumber(data.totalVarianceHours);
                $('#totalVariance').text(variance + ' Hrs');

                let varianceBox = $('#varianceBox');
                varianceBox.removeClass('bg-danger bg-success bg-secondary');

                if (variance > 0) {
                    varianceBox.addClass('bg-danger');
                } else if (variance < 0) {
                    varianceBox.addClass('bg-success');
                } else {
                    varianceBox.addClass('bg-secondary');
                }

                $('#accuracy').text(safeNumber(data.accuracy) + ' %');

                // ================= DESTROY OLD CHARTS =================
                destroyChart(timeChart);
                destroyChart(statusChart);
                destroyChart(performanceChart);
                destroyChart(monthlyTrendChart);

                // ================= TIME CATEGORY =================
                let timeCategory = data.timeCategory || {overdue:0, ontime:0, underdue:0};

                timeChart = new Chart(document.getElementById('timeCategoryChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Overdue','Ontime','Underdue'],
                        datasets: [{
                            data: [
                                safeNumber(timeCategory.overdue),
                                safeNumber(timeCategory.ontime),
                                safeNumber(timeCategory.underdue)
                            ],
                            backgroundColor: ['#dc3545','#28a745','#17a2b8']
                        }]
                    }
                });

                // ================= TASK STATUS =================
                let taskStatus = data.taskStatus || {};

                statusChart = new Chart(document.getElementById('taskStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(taskStatus),
                        datasets: [{
                            data: Object.values(taskStatus),
                            backgroundColor: ['#007bff','#ffc107','#6c757d','#28a745','#dc3545']
                        }]
                    }
                });

                // ================= PERFORMANCE SPLIT =================
                let performanceSplit = data.performanceSplit || {overrun:0, ontime:0};

                performanceChart = new Chart(document.getElementById('performanceChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Overrun','On Time'],
                        datasets: [{
                            data: [
                                safeNumber(performanceSplit.overrun),
                                safeNumber(performanceSplit.ontime)
                            ],
                            backgroundColor: ['#dc3545','#28a745']
                        }]
                    }
                });

                // ================= MONTHLY TREND =================
                let monthlyTrend = data.monthlyTrend || [];

                let months = monthlyTrend.map(item => item.month);
                let estimated = monthlyTrend.map(item => safeNumber(item.estimated) / 60);
                let actual = monthlyTrend.map(item => safeNumber(item.actual) / 60);

                monthlyTrendChart = new Chart(document.getElementById('monthlyTrendChart'), {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: 'Estimated Hours',
                                data: estimated,
                                borderColor: '#17a2b8',
                                backgroundColor: 'rgba(23,162,184,0.1)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Actual Hours',
                                data: actual,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220,53,69,0.1)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // ================= EMPLOYEE TABLE =================
                let empHtml = '';
                (data.employeeLeaderboard || []).forEach(function(emp){
                    empHtml += `
                        <tr>
                            <td>${emp.name}</td>
                            <td>${(safeNumber(emp.total_estimated)/60).toFixed(2)}</td>
                            <td>${(safeNumber(emp.total_actual)/60).toFixed(2)}</td>
                            <td>${emp.efficiency ?? 0} %</td>
                        </tr>
                    `;
                });

                if(empHtml === ''){
                    empHtml = `<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>`;
                }

                $('#employeeLeaderboardBody').html(empHtml);

                // ================= TEAM TABLE =================
                let teamHtml = '';
                (data.teamRanking || []).forEach(function(team){
                    teamHtml += `
                        <tr>
                            <td>${team.team}</td>
                            <td>${(safeNumber(team.total_estimated)/60).toFixed(2)}</td>
                            <td>${(safeNumber(team.total_actual)/60).toFixed(2)}</td>
                            <td>${team.efficiency ?? 0} %</td>
                        </tr>
                    `;
                });

                if(teamHtml === ''){
                    teamHtml = `<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>`;
                }

                $('#teamRankingBody').html(teamHtml);
            },
            complete: function(){
                $('#loader').addClass('d-none');
            }
        });
    }

    loadDashboard();

    $('select, input').on('change', function(){
        loadDashboard();
    });

});
</script>

@endsection