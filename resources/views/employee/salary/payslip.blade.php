<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $month }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .payslip {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .payslip-title {
            font-size: 20px;
            color: #666;
        }
        .employee-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        .salary-details {
            margin-bottom: 30px;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th,
        .salary-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .salary-table th {
            background-color: #f5f5f5;
        }
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media print {
            body {
                padding: 0;
            }
            .payslip {
                border: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="payslip">
        <div class="header">
            <div class="company-name">Momo Shop</div>
            <div class="payslip-title">Monthly Payslip</div>
            <div>{{ $month }}</div>
        </div>

        <div class="employee-info">
            <div class="info-row">
                <div class="info-label">Employee ID:</div>
                <div>{{ $employee->employee_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Name:</div>
                <div>{{ $employee->user->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Position:</div>
                <div>{{ $employee->position }}</div>
            </div>
        </div>

        <div class="salary-details">
            <table class="salary-table">
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Hours Worked</td>
                    <td>{{ $hours }} hours</td>
                </tr>
                <tr>
                    <td>Hourly Rate</td>
                    <td>${{ number_format($hourly_rate, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Salary</td>
                    <td>${{ number_format($total_salary, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>This is a computer-generated document and does not require a signature.</p>
            <p>Generated on: {{ $generated_at }}</p>
        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()">Print Payslip</button>
        </div>
    </div>
</body>
</html> 