<?php
// terms.blade.php
?>
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-primary text-white py-4">
                    <h1 class="h3 mb-0 font-weight-bold">Terms and Conditions</h1>
                </div>
                <div class="card-body p-4 p-md-5">
                    <p class="lead text-muted">Last updated: {{ date('F d, Y') }}</p>
                    
                    <hr class="my-4">

                    <h2 class="h4 font-weight-bold mb-3">1. Identification</h2>
                    <p>Welcome to <strong>{{ config('app.name') }}</strong>. These terms and conditions outline the rules and regulations for the use of our website and services.</p>

                    <h2 class="h4 font-weight-bold mt-4 mb-3">2. Service Description</h2>
                    <p>We provide a platform for ordering food items and managing momo-related products. By accessing this website, we assume you accept these terms and conditions. Do not continue to use {{ config('app.name') }} if you do not agree to all of the terms and conditions stated on this page.</p>

                    <h2 class="h4 font-weight-bold mt-4 mb-3">3. User Responsibilities</h2>
                    <p>Users must provide accurate information when registering or placing orders. Any misuse of the account or fraudulent activity may result in termination of services.</p>

                    <h2 class="h4 font-weight-bold mt-4 mb-3">4. Intellectual Property</h2>
                    <p>Unless otherwise stated, {{ config('app.name') }} and/or its licensors own the intellectual property rights for all material on this website. All intellectual property rights are reserved.</p>

                    <div class="alert alert-info mt-5 border-0">
                        <p class="mb-0"><i class="fas fa-info-circle mr-2"></i> This is a placeholder terms of service page. Please replace this content with your actual legal documentation as required by your jurisdiction.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
