@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 text-primary">
                        <i class="fas fa-gift me-2"></i>
                        Referral Program Settings
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.referral-settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Referred User Rewards -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Referred User Rewards (Money)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Reward Type</th>
                                                <th>Amount (Rs.)</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label for="referral_welcome_bonus" class="form-label fw-bold">
                                                        Welcome Bonus
                                                    </label>
                                                </td>
                                                <td style="width: 200px;">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rs.</span>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="referral_welcome_bonus" 
                                                               name="referral_welcome_bonus" 
                                                               value="{{ $settings['referral_welcome_bonus'] }}" 
                                                               min="0" 
                                                               step="1">
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Amount given to user when they register with a referral code
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="referral_first_order_bonus" class="form-label fw-bold">
                                                        First Order Bonus
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rs.</span>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="referral_first_order_bonus" 
                                                               name="referral_first_order_bonus" 
                                                               value="{{ $settings['referral_first_order_bonus'] }}" 
                                                               min="0" 
                                                               step="1">
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Amount given to user for their first order
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="referral_subsequent_order_bonus" class="form-label fw-bold">
                                                        Subsequent Order Bonus
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rs.</span>
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="referral_subsequent_order_bonus" 
                                                               name="referral_subsequent_order_bonus" 
                                                               value="{{ $settings['referral_subsequent_order_bonus'] }}" 
                                                               min="0" 
                                                               step="1">
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Amount given to user for each subsequent order
                                                    </small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Creator Rewards -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-crown me-2"></i>
                                    Creator Rewards (Points)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Reward Type</th>
                                                <th>Points</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label for="creator_referral_bonus" class="form-label fw-bold">
                                                        Referral Bonus
                                                    </label>
                                                </td>
                                                <td style="width: 200px;">
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="creator_referral_bonus" 
                                                               name="creator_referral_bonus" 
                                                               value="{{ $settings['creator_referral_bonus'] }}" 
                                                               min="0" 
                                                               step="1">
                                                        <span class="input-group-text">points</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Points given to creator when someone uses their referral code
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="creator_first_order_bonus" class="form-label fw-bold">
                                                        First Order Bonus
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="creator_first_order_bonus" 
                                                               name="creator_first_order_bonus" 
                                                               value="{{ $settings['creator_first_order_bonus'] }}" 
                                                               min="0" 
                                                               step="1">
                                                        <span class="input-group-text">points</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Points given to creator for referred user's first order
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="creator_subsequent_order_bonus" class="form-label fw-bold">
                                                        Subsequent Order Bonus
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="creator_subsequent_order_bonus" 
                                                               name="creator_subsequent_order_bonus" 
                                                               value="{{ $settings['creator_subsequent_order_bonus'] }}" 
                                                               min="0" 
                                                               step="1">
                                                        <span class="input-group-text">points</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Points given to creator for each subsequent order
                                                    </small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="max_referral_orders" class="form-label fw-bold">
                                                        Maximum Referral Orders
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="max_referral_orders" 
                                                               name="max_referral_orders" 
                                                               value="{{ $settings['max_referral_orders'] }}" 
                                                               min="1" 
                                                               max="100" 
                                                               step="1">
                                                        <span class="input-group-text">orders</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        Maximum number of orders for which bonuses are given
                                                    </small>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Card -->
                        <div class="card mb-4 border-0 bg-light">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Program Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">For Referred Users:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Get Rs. {{ $settings['referral_welcome_bonus'] }} when they register
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Get Rs. {{ $settings['referral_first_order_bonus'] }} for their first order
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Get Rs. {{ $settings['referral_subsequent_order_bonus'] }} for each of their next {{ $settings['max_referral_orders'] - 1 }} orders
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold">For Creators:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Get {{ $settings['creator_referral_bonus'] }} points for each referral
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Get {{ $settings['creator_first_order_bonus'] }} points for first order
                                            </li>
                                            <li>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                Get {{ $settings['creator_subsequent_order_bonus'] }} points for each of their next {{ $settings['max_referral_orders'] - 1 }} orders
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 