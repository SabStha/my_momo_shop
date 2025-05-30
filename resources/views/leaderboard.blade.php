@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Top 10 Creators</h1>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Points</th>
                    <th>Discount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($creators as $i => $creator)
                <tr @if($i === 0) style="background: linear-gradient(90deg, #ffd700 0%, #fffbe6 100%); font-weight:bold;" @elseif($i === 1) style="background: linear-gradient(90deg, #c0c0c0 0%, #f8f9fa 100%); font-weight:bold;" @elseif($i === 2) style="background: linear-gradient(90deg, #cd7f32 0%, #f8f9fa 100%); font-weight:bold;" @endif>
                    <td>
                        @if($i === 0)
                            <span class="me-1" title="Gold"><i class="fas fa-crown text-warning"></i></span>
                        @elseif($i === 1)
                            <span class="me-1" title="Silver"><i class="fas fa-medal text-secondary"></i></span>
                        @elseif($i === 2)
                            <span class="me-1" title="Bronze"><i class="fas fa-medal text-warning" style="color:#cd7f32 !important;"></i></span>
                        @endif
                        #{{ $i + 1 }}
                    </td>
                    <td>{{ $creator->user->name }}</td>
                    <td>{{ $creator->points }}</td>
                    <td>
                        {{ config('referral.discounts.' . ($i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : 'default')))) }}%
                    </td>
                    <td>
                        @if($creator->isTrending())
                            <span class="badge bg-danger">🔥 Trending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 