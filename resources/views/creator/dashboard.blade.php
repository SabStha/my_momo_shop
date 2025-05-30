@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('creator-dashboard.logout') }}" class="btn btn-danger">Logout</a>
    </div>
    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <!-- Creator Profile Card -->
            <div class="card shadow-sm d-flex flex-column align-items-center p-3">
                <img src="{{ $creator && $creator->avatar ? asset('storage/' . $creator->avatar) : asset('images/avatar-placeholder.png') }}" class="rounded-circle mb-3" alt="Avatar" style="width: 120px; height: 120px; object-fit: cover;">
                <h4 class="mb-1">{{ $creator->user->name ?? 'N/A' }}</h4>
                <p class="text-muted mb-2">{{ $creator->bio ?? 'No bio yet.' }}</p>
                <div class="mb-2">
                    <span class="badge bg-primary">Referral Code: {{ $creator->code ?? 'N/A' }}</span>
                </div>
                @if(isset($rank) && $rank <= 10)
                    <div class="alert alert-info py-1 px-2 mb-2">Leaderboard Rank: #{{ $rank }}</div>
                @endif
                <button id="generate-referral" class="btn btn-primary btn-lg w-100 mt-3">Generate Referral Coupon</button>
                <div id="coupon-code" class="alert d-none mt-2"></div>
                <div class="mt-3">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="profilePhotoDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Add/Edit Profile Photo
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="profilePhotoDropdown">
                            <li>
                                <form action="{{ route('creator-dashboard.update-profile-photo') }}" method="POST" enctype="multipart/form-data" class="p-2">
                                    @csrf
                                    <div class="mb-2">
                                        <label for="avatar" class="form-label">Upload Photo</label>
                                        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <!-- Referral Statistics -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Total Referrals</h6>
                            <h3>{{ $referrals->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Pending</h6>
                            <h3>{{ $referrals->where('status', 'pending')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Used</h6>
                            <h3>{{ $referrals->where('status', 'used')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h6 class="card-title">Expired</h6>
                            <h3>{{ $referrals->where('status', 'expired')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Referral Chart -->
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Referrals (Last 7 Days)</h6>
                    <canvas id="referralChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Referral Links Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Your Referral Links</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Referral Code</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th>Link</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($referrals as $ref)
                        <tr>
                            <td>{{ $ref->code }}</td>
                            <td>
                                <span class="badge 
                                    @if($ref->status == 'used') bg-success
                                    @elseif($ref->status == 'pending') bg-warning text-dark
                                    @elseif($ref->status == 'expired') bg-secondary
                                    @else bg-light text-dark @endif">
                                    {{ ucfirst($ref->status) }}
                                </span>
                            </td>
                            <td>{{ $ref->created_at ? $ref->created_at->format('Y-m-d') : '' }}</td>
                            <td><span class="text-break">{{ url('/?ref=' . $ref->code) }}</span></td>
                            <td><button class="btn btn-outline-secondary btn-sm copy-link" data-link="{{ url('/?ref=' . $ref->code) }}">Copy Link</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Leaderboard Section -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Referral Leaderboard</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Rank</th>
                            <th>Creator</th>
                            <th>Points</th>
                            <th>User Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCreators as $i => $c)
                        <tr @if($c->id == ($creator->id ?? null)) class="table-info" @endif>
                            <td>#{{ $i + 1 }}</td>
                            <td>{{ $c->user->name }}</td>
                            <td>{{ $c->points ?? 0 }}</td>
                            <td>{{ $c->referral_count ?? 0 }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Referral Chart
    const ctx = document.getElementById('referralChart').getContext('2d');
    const referralChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($referralChartLabels ?? []),
            datasets: [{
                label: 'Referrals',
                data: @json($referralChartData ?? []),
                fill: true,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Copy Link Button
    document.querySelectorAll('.copy-link').forEach(btn => {
        btn.addEventListener('click', function() {
            navigator.clipboard.writeText(this.dataset.link).then(() => {
                this.textContent = 'Copied!';
                setTimeout(() => { this.textContent = 'Copy Link'; }, 1500);
            });
        });
    });

    // Generate Referral Coupon Button
    var generateBtn = document.getElementById('generate-referral');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            fetch('{{ route('creator-dashboard.generate-referral') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => {
                if (!response.ok) return response.json().then(err => { throw err; });
                return response.json();
            })
            .then(data => {
                document.getElementById('coupon-code').textContent = 'Coupon Code: ' + data.coupon_code;
                document.getElementById('coupon-code').classList.remove('d-none', 'alert-danger');
                document.getElementById('coupon-code').classList.add('alert-success');
            })
            .catch(error => {
                let msg = error.message || (error.error || 'You are not registered as a creator. Please create a creator profile.');
                document.getElementById('coupon-code').textContent = msg;
                document.getElementById('coupon-code').classList.remove('d-none', 'alert-success');
                document.getElementById('coupon-code').classList.add('alert-danger');
            });
        });
    }
</script>
@endpush 