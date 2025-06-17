@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Churn Predictions</h1>
        <form action="{{ route('admin.churn.update') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Update Predictions
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($highRiskCustomers as $prediction)
                <li>
                    <a href="{{ route('admin.churn.show', $prediction->customer) }}" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-xl font-semibold text-gray-600">
                                                {{ substr($prediction->customer->first_name, 0, 1) }}{{ substr($prediction->customer->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $prediction->customer->first_name }} {{ $prediction->customer->last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $prediction->customer->email }}
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <div class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($prediction->churn_probability >= 80)
                                            bg-red-100 text-red-800
                                        @elseif($prediction->churn_probability >= 60)
                                            bg-yellow-100 text-yellow-800
                                        @else
                                            bg-green-100 text-green-800
                                        @endif">
                                        {{ number_format($prediction->churn_probability, 1) }}% Churn Risk
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        Last updated: {{ $prediction->last_updated->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            @empty
                <li class="px-4 py-5 sm:px-6">
                    <div class="text-center text-gray-500">
                        No high-risk customers found.
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection 