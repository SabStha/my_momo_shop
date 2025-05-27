<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .schedule-slot {
            padding: 4px;
            background-color: #f8f9fa;
            border-radius: 2px;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Employee Schedule</h1>
        <p>{{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                    <th>{{ $date->format('D, M j') }}</th>
                @endfor
                <th>Total Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $employeeId => $employeeSchedules)
                @php
                    $employee = $employeeSchedules->first()->employee;
                    $totalHours = 0;
                @endphp
                <tr>
                    <td>{{ $employee->name }}</td>
                    @for($date = $startDate->copy(); $date <= $endDate; $date->addDay())
                        <td>
                            @php
                                $schedule = $employeeSchedules->firstWhere('work_date', $date->format('Y-m-d'));
                            @endphp
                            @if($schedule)
                                <div class="schedule-slot">
                                    {{ $schedule->shift_start->format('g:i A') }} - {{ $schedule->shift_end->format('g:i A') }}
                                    @php
                                        $totalHours += $schedule->getDurationInHours();
                                    @endphp
                                </div>
                            @endif
                        </td>
                    @endfor
                    <td>{{ number_format($totalHours, 1) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Weekly Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Total Hours</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $employeeId => $employeeSchedules)
                    @php
                        $employee = $employeeSchedules->first()->employee;
                        $totalHours = $employeeSchedules->sum(function($schedule) {
                            return $schedule->getDurationInHours();
                        });
                    @endphp
                    <tr>
                        <td>{{ $employee->name }}</td>
                        <td>{{ number_format($totalHours, 1) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html> 