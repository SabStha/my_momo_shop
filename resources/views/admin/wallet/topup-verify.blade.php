@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Wallet Verification</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        You are now authenticated for wallet operations. You can proceed with wallet management.
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.wallet.index') }}" class="btn btn-primary">
                            Go to Wallet Management
                        </a>
                        <a href="{{ route('admin.wallet.topup.logout') }}" class="btn btn-danger">
                            Logout from Wallet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 