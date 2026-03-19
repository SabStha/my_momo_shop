@php /* Physical Drawer Denominations Modal */ @endphp
<div id="physicalDrawerDenominationsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[98vh] overflow-hidden flex flex-col">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-600 to-yellow-400 shrink-0">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-cash-register mr-2"></i> Cash Drawer Count
                </h3>
                <button id="closePhysicalDrawerDenominationsModalBtn" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 overflow-y-auto flex-1">

            {{-- Security section --}}
            <div class="mb-5 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="font-medium text-yellow-900 mb-2">🔐 Security Required</h4>
                <p class="text-yellow-800 text-sm mb-3">Enter the security password to save counts.</p>
                <div class="flex items-center gap-3">
                    <input type="password" id="denominationPassword"
                           class="flex-1 px-3 py-2 border border-yellow-300 rounded-md focus:ring-2 focus:ring-yellow-500"
                           placeholder="Enter security password">
                    <button id="validatePasswordBtn"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors">
                        Validate
                    </button>
                </div>
                <div id="passwordError" class="mt-2 text-red-600 text-sm hidden">Invalid password. Please try again.</div>
            </div>

            {{-- Denomination table --}}
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="text-left px-3 py-2 font-semibold">Denomination</th>
                        <th class="text-right px-3 py-2 font-semibold">Opening<br>Count</th>
                        <th class="text-right px-3 py-2 font-semibold">Opening<br>Value</th>
                        <th class="text-right px-3 py-2 font-semibold">Current<br>Count</th>
                        <th class="text-right px-3 py-2 font-semibold">Current<br>Value</th>
                    </tr>
                </thead>
                <tbody id="denomTableBody" class="divide-y divide-gray-100">
                    @foreach([1000,500,100,50,20,10,5,2,1] as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium text-gray-800">Rs {{ $d }}</td>
                        <td class="px-3 py-2 text-right text-gray-600" id="open-{{ $d }}">0</td>
                        <td class="px-3 py-2 text-right text-gray-500" id="open-val-{{ $d }}">Rs 0</td>
                        <td class="px-3 py-2 text-right">
                            <input type="number" id="curr-{{ $d }}"
                                   class="w-20 px-2 py-1 border border-gray-300 rounded text-right text-sm"
                                   min="0" value="0">
                        </td>
                        <td class="px-3 py-2 text-right font-medium text-gray-700" id="curr-val-{{ $d }}">Rs 0</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-300 bg-gray-50 text-sm font-semibold">
                    <tr class="border-t border-gray-200">
                        <td colspan="2" class="px-3 py-2 text-gray-700">Opening Total</td>
                        <td class="px-3 py-2 text-right text-gray-800" id="opening-total">Rs 0</td>
                        <td class="px-3 py-2 text-gray-700">Current Total</td>
                        <td class="px-3 py-2 text-right text-gray-800" id="current-total">Rs 0</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-3 py-2 text-gray-700">Expected (Opening + Cash Payments)</td>
                        <td class="px-3 py-2 text-right text-blue-700" id="expected-total">Rs 0</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-3 py-2 text-gray-700">Difference</td>
                        <td class="px-3 py-2 text-right font-bold" id="difference">Rs 0</td>
                    </tr>
                </tfoot>
            </table>

        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-3 shrink-0">
            <button id="saveDenominationsBtn"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                Save Counts
            </button>
            <button id="closePhysicalDrawerDenominationsModalBtn2"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                Close
            </button>
        </div>

    </div>
</div>
