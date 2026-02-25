@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Performance Dashboard</h4>
    </div>

    {{-- FILTERS --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">

                <div class="col-md-3">
                    <label>Vertical</label>
                    <select id="verticalFilter" class="form-control">
                        <option value="">All</option>
                        @foreach($verticals as $v)
                            <option value="{{ $v }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Team</label>
                    <select id="teamFilter" class="form-control">
                        <option value="">All</option>
                        @foreach($teams as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Employee</label>
                    <select id="employeeFilter" class="form-control">
                        <option value="">All</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Date Range</label>
                    <div class="d-flex">
                        <input type="date" id="startFrom" class="form-control me-2">
                        <input type="date" id="startTo" class="form-control">
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- LOADER --}}
    <div id="loader" style="display:none;text-align:center" class="mb-3">
        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
    </div>

    {{-- KPI CARDS --}}
    <div class="row">

        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3 id="totalTasks">0</h3>
                    <p>Total Tasks</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="totalEmployees">0</h3>
                    <p>Total Employees</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalEstimated">0</h3>
                    <p>Estimated Hours</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalActual">0</h3>
                    <p>Actual Hours</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="totalVariance">0</h3>
                    <p>Variance Hours</p>
                </div>
            </div>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="row mt-4">

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Time Category Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="timeCategoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Task Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection


@section('scripts')

<!-- jQuery (REQUIRED) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function(){

    let timeChart;
    let statusChart;

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
                $('#loader').show();
            },
            success: function(data){

                $('#totalTasks').text(data.totalTasks);
                $('#totalEmployees').text(data.totalEmployees);
                $('#totalEstimated').text(data.totalEstimatedHours + ' Hrs');
                $('#totalActual').text(data.totalActualHours + ' Hrs');
                $('#totalVariance').text(data.totalVarianceHours + ' Hrs');

                if(timeChart) timeChart.destroy();
                if(statusChart) statusChart.destroy();

                timeChart = new Chart(document.getElementById('timeCategoryChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Overdue','Ontime','Underdue'],
                        datasets: [{
                            data: [
                                data.timeCategory.overdue,
                                data.timeCategory.ontime,
                                data.timeCategory.underdue
                            ],
                            backgroundColor: ['#dc3545','#28a745','#17a2b8']
                        }]
                    }
                });

                statusChart = new Chart(document.getElementById('taskStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: Object.keys(data.taskStatus),
                        datasets: [{
                            data: Object.values(data.taskStatus),
                            backgroundColor: ['#007bff','#ffc107','#6c757d','#28a745','#dc3545']
                        }]
                    }
                });

            },
            complete: function(){
                $('#loader').hide();
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