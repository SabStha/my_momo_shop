@php /* Alert Settings Modal Partial */ @endphp
<!-- Alert Settings Modal -->
<div id="alertSettingsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[98vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-600 to-yellow-400">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-bell mr-2"></i>
                    Denomination Alert Settings
                </h3>
                <button id="closeAlertSettingsModalBtn" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <form id="alertSettingsForm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left py-2">Denomination</th>
                            <th class="text-left py-2">Low Threshold</th>
                            <th class="text-left py-2">High Threshold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([1000,500,100,50,20,10,5,2,1] as $denom)
                        <tr>
                            <td class="py-2 font-medium">Rs {{ $denom }}</td>
                            <td class="py-2">
                                <input type="number" name="low_{{ $denom }}" id="low_{{ $denom }}" class="w-20 px-2 py-1 border border-gray-300 rounded" min="0" placeholder="-">
                            </td>
                            <td class="py-2">
                                <input type="number" name="high_{{ $denom }}" id="high_{{ $denom }}" class="w-20 px-2 py-1 border border-gray-300 rounded" min="0" placeholder="-">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 font-medium flex items-center">
                        <span>Save Settings</span>
                        <span id="alertSettingsLoadingSpinner" class="hidden ml-2"><i class="fas fa-spinner fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 