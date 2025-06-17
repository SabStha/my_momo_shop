@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Segments & Insights</h3>
                </div>
                <div class="card-body">
                    <form id="exportForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="branch_id">Branch</label>
                                    <select class="form-control" id="branch_id" name="branch_id">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="segment_type">Segment Type</label>
                                    <select class="form-control" id="segment_type" name="segment_type">
                                        <option value="churned">Churned Customers</option>
                                        <option value="at-risk">At-Risk Customers</option>
                                        <option value="loyal">Loyal Customers</option>
                                        <option value="new">New Customers</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="days_inactive">Days Inactive</label>
                                    <input type="number" class="form-control" id="days_inactive" name="days_inactive" value="30" min="1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="format">Export Format</label>
                                    <select class="form-control" id="format" name="format">
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Export Data
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-user-slash"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Churned Customers</span>
                                    <span class="info-box-number" id="churnedCount">Loading...</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">At-Risk Customers</span>
                                    <span class="info-box-number" id="atRiskCount">Loading...</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Loyal Customers</span>
                                    <span class="info-box-number" id="loyalCount">Loading...</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Segment Insights</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <canvas id="segmentDistributionChart"></canvas>
                                        </div>
                                        <div class="col-md-6">
                                            <canvas id="customerValueChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportForm = document.getElementById('exportForm');
    const segmentType = document.getElementById('segment_type');
    const daysInactive = document.getElementById('days_inactive');
    const branchId = document.getElementById('branch_id');
    const format = document.getElementById('format');

    // Initialize charts
    const segmentDistributionChart = new Chart(
        document.getElementById('segmentDistributionChart'),
        {
            type: 'pie',
            data: {
                labels: ['Churned', 'At-Risk', 'Loyal', 'New'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#dc3545', '#ffc107', '#28a745', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Customer Segment Distribution'
                    }
                }
            }
        }
    );

    const customerValueChart = new Chart(
        document.getElementById('customerValueChart'),
        {
            type: 'bar',
            data: {
                labels: ['Churned', 'At-Risk', 'Loyal', 'New'],
                datasets: [{
                    label: 'Average Customer Value',
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#dc3545', '#ffc107', '#28a745', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Average Customer Value by Segment'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Value (GHS)'
                        }
                    }
                }
            }
        }
    );

    // Load segment data
    function loadSegmentData() {
        const params = new URLSearchParams({
            branch_id: branchId.value,
            segment_type: segmentType.value,
            days_inactive: daysInactive.value,
            format: 'json'
        });

        fetch(`/admin/customer-segments/export?${params}`)
            .then(response => response.json())
            .then(data => {
                // Update counts
                const counts = {
                    churned: 0,
                    atRisk: 0,
                    loyal: 0,
                    new: 0
                };

                const values = {
                    churned: 0,
                    atRisk: 0,
                    loyal: 0,
                    new: 0
                };

                data.forEach(customer => {
                    const segment = customer.Segment.toLowerCase();
                    counts[segment]++;
                    values[segment] += parseFloat(customer['Lifetime Value']);
                });

                // Update info boxes
                document.getElementById('churnedCount').textContent = counts.churned;
                document.getElementById('atRiskCount').textContent = counts.atRisk;
                document.getElementById('loyalCount').textContent = counts.loyal;

                // Update charts
                segmentDistributionChart.data.datasets[0].data = [
                    counts.churned,
                    counts.atRisk,
                    counts.loyal,
                    counts.new
                ];
                segmentDistributionChart.update();

                customerValueChart.data.datasets[0].data = [
                    counts.churned ? values.churned / counts.churned : 0,
                    counts.atRisk ? values.atRisk / counts.atRisk : 0,
                    counts.loyal ? values.loyal / counts.loyal : 0,
                    counts.new ? values.new / counts.new : 0
                ];
                customerValueChart.update();
            })
            .catch(error => {
                console.error('Error loading segment data:', error);
                alert('Error loading segment data. Please try again.');
            });
    }

    // Handle form submission
    exportForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const params = new URLSearchParams({
            branch_id: branchId.value,
            segment_type: segmentType.value,
            days_inactive: daysInactive.value,
            format: format.value
        });

        window.location.href = `/admin/customer-segments/export?${params}`;
    });

    // Load initial data
    loadSegmentData();

    // Update data when filters change
    [segmentType, daysInactive, branchId].forEach(element => {
        element.addEventListener('change', loadSegmentData);
    });
});
</script>
@endpush 