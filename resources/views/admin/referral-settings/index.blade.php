@extends('layouts.admin')

@section('content')
<div class="px-6 py-10 mx-auto max-w-screen-xl">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-indigo-50">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-gift mr-2 text-indigo-600"></i> Referral Program Settings
            </h2>
        </div>
        <div class="px-6 py-6">
            <form action="{{ route('admin.referral-settings.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Section 1: Referred User Rewards -->
                <div class="border rounded-md shadow">
                    <div class="bg-blue-100 px-4 py-3 border-b">
                        <h3 class="font-semibold text-blue-800 text-lg flex items-center">
                            <i class="fas fa-user-plus mr-2"></i> Referred User Rewards (Money)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                            <thead class="bg-gray-50 text-gray-700 font-medium">
                                <tr>
                                    <th class="px-6 py-3 w-1/4">Reward Type</th>
                                    <th class="px-6 py-3 w-1/4">Amount (Rs.)</th>
                                    <th class="px-6 py-3">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-6 py-4 font-medium">Welcome Bonus</td>
                                    <td class="px-6 py-4">
                                        <div class="flex">
                                            <span class="inline-flex items-center px-2 bg-gray-100 border rounded-l">Rs.</span>
                                            <input type="number" name="referral_welcome_bonus" class="border-t border-b border-r rounded-r px-2 py-1 w-full" value="{{ $settings['referral_welcome_bonus'] }}" min="0">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Given when user registers with a referral code</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 font-medium">First Order Bonus</td>
                                    <td class="px-6 py-4">
                                        <div class="flex">
                                            <span class="inline-flex items-center px-2 bg-gray-100 border rounded-l">Rs.</span>
                                            <input type="number" name="referral_first_order_bonus" class="border-t border-b border-r rounded-r px-2 py-1 w-full" value="{{ $settings['referral_first_order_bonus'] }}" min="0">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Given on user's first order</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 font-medium">Subsequent Orders</td>
                                    <td class="px-6 py-4">
                                        <div class="flex">
                                            <span class="inline-flex items-center px-2 bg-gray-100 border rounded-l">Rs.</span>
                                            <input type="number" name="referral_subsequent_order_bonus" class="border-t border-b border-r rounded-r px-2 py-1 w-full" value="{{ $settings['referral_subsequent_order_bonus'] }}" min="0">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Bonus for every repeat order</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 2: Creator Rewards -->
                <div class="border rounded-md shadow">
                    <div class="bg-green-100 px-4 py-3 border-b">
                        <h3 class="font-semibold text-green-800 text-lg flex items-center">
                            <i class="fas fa-crown mr-2"></i> Creator Rewards (Points)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                            <thead class="bg-gray-50 text-gray-700 font-medium">
                                <tr>
                                    <th class="px-6 py-3 w-1/4">Reward Type</th>
                                    <th class="px-6 py-3 w-1/4">Points</th>
                                    <th class="px-6 py-3">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-6 py-4 font-medium">Referral Bonus</td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="creator_referral_bonus" class="border px-2 py-1 w-full rounded" value="{{ $settings['creator_referral_bonus'] }}" min="0">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">When their referral code is used</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 font-medium">First Order Bonus</td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="creator_first_order_bonus" class="border px-2 py-1 w-full rounded" value="{{ $settings['creator_first_order_bonus'] }}" min="0">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Referred user’s first order</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 font-medium">Subsequent Orders</td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="creator_subsequent_order_bonus" class="border px-2 py-1 w-full rounded" value="{{ $settings['creator_subsequent_order_bonus'] }}" min="0">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Each repeat order</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 font-medium">Max Orders</td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="max_referral_orders" class="border px-2 py-1 w-full rounded" value="{{ $settings['max_referral_orders'] }}" min="1" max="100">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">Max orders per referral for bonuses</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section 3: Program Summary -->
                <div class="border rounded-md shadow">
                    <div class="bg-gray-100 px-4 py-3 border-b">
                        <h3 class="font-semibold text-gray-800 text-lg flex items-center">
                            <i class="fas fa-info-circle mr-2"></i> Program Summary
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <h4 class="text-blue-700 font-semibold mb-2">Referred User</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li>✔ Welcome Bonus: Rs. {{ $settings['referral_welcome_bonus'] }}</li>
                                <li>✔ First Order Bonus: Rs. {{ $settings['referral_first_order_bonus'] }}</li>
                                <li>✔ Subsequent Orders: Rs. {{ $settings['referral_subsequent_order_bonus'] }}</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-green-700 font-semibold mb-2">Creator</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li>✔ Referral: {{ $settings['creator_referral_bonus'] }} pts</li>
                                <li>✔ First Order: {{ $settings['creator_first_order_bonus'] }} pts</li>
                                <li>✔ Subsequent: {{ $settings['creator_subsequent_order_bonus'] }} pts</li>
                                <li>✔ Max Orders: {{ $settings['max_referral_orders'] }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="text-right pt-4">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-6 rounded shadow">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
