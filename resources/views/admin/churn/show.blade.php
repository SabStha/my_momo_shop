@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.churn.index') }}" class="text-blue-600 hover:text-blue-900">
            &larr; Back to Churn Predictions
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Customer Churn Analysis
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Detailed churn risk assessment for {{ $customer->first_name }} {{ $customer->last_name }}
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Churn Probability
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="flex items-center">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full 
                                    @if($prediction->churn_probability >= 80)
                                        bg-red-600
                                    @elseif($prediction->churn_probability >= 60)
                                        bg-yellow-500
                                    @else
                                        bg-green-500
                                    @endif"
                                    style="width: {{ $prediction->churn_probability }}%">
                                </div>
                            </div>
                            <span class="ml-2 font-semibold">{{ number_format($prediction->churn_probability, 1) }}%</span>
                        </div>
                    </dd>
                </div>

                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Risk Factors
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <span class="ml-2 flex-1 w-0 truncate">
                                        Days Since Last Purchase
                                    </span>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="font-medium text-gray-900">
                                        {{ $prediction->risk_factors['days_since_last_purchase'] }} days
                                    </span>
                                </div>
                            </li>
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <span class="ml-2 flex-1 w-0 truncate">
                                        Purchase Frequency
                                    </span>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="font-medium text-gray-900">
                                        {{ number_format($prediction->risk_factors['purchase_frequency'], 1) }} per month
                                    </span>
                                </div>
                            </li>
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <span class="ml-2 flex-1 w-0 truncate">
                                        Average Order Value
                                    </span>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="font-medium text-gray-900">
                                        Rs {{ number_format($prediction->risk_factors['average_order_value'], 2) }}
                                    </span>
                                </div>
                            </li>
                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <span class="ml-2 flex-1 w-0 truncate">
                                        Engagement Score
                                    </span>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="font-medium text-gray-900">
                                        {{ number_format($prediction->risk_factors['engagement_score'] * 100, 1) }}%
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </dd>
                </div>

                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">
                        Last Updated
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $prediction->last_updated->format('F j, Y g:i A') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="mt-8">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Recommended Actions
        </h3>
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <ul class="divide-y divide-gray-200">
                @if($prediction->churn_probability >= 80)
                    <li class="px-4 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Immediate Personal Outreach
                                </p>
                                <p class="text-sm text-gray-500">
                                    Schedule a personal call or visit to understand customer concerns
                                </p>
                            </div>
                        </div>
                    </li>
                @endif
                
                @if($prediction->risk_factors['days_since_last_purchase'] > 60)
                    <li class="px-4 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Special Re-engagement Offer
                                </p>
                                <p class="text-sm text-gray-500">
                                    Send a personalized discount or special offer to encourage return
                                </p>
                            </div>
                        </div>
                    </li>
                @endif
                
                @if($prediction->risk_factors['engagement_score'] < 0.5)
                    <li class="px-4 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" />
                                    <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    Increase Engagement
                                </p>
                                <p class="text-sm text-gray-500">
                                    Invite to loyalty program and send personalized content
                                </p>
                            </div>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
@endsection 