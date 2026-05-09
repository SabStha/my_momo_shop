<div id="globalConfirmModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden z-50 transition-opacity" role="dialog" aria-modal="true" aria-labelledby="globalConfirmModalTitle">
    <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeConfirmModal()"></div>

        <!-- Tricking the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full z-10" role="document">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="globalConfirmModalTitle">Confirm Action</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeConfirmModal()">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <p class="text-sm text-gray-500" id="globalConfirmModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex flex-row-reverse sm:px-6">
                <!-- Standard Laravel form submission -->
                <form id="globalConfirmModalForm" method="POST" class="ml-3">
                    @csrf
                    <input type="hidden" name="_method" id="globalConfirmModalMethod" value="POST">
                    <button type="submit" id="globalConfirmModalConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                        Confirm
                    </button>
                </form>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" onclick="closeConfirmModal()">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openConfirmModal(config) {
        // Expected config: title, message, url, method, confirmText, confirmColor (red, blue, etc)
        document.getElementById('globalConfirmModalTitle').innerText = config.title || 'Confirm Action';
        document.getElementById('globalConfirmModalMessage').innerText = config.message || 'Are you sure you want to proceed?';
        
        const form = document.getElementById('globalConfirmModalForm');
        form.action = config.url;
        
        const methodInput = document.getElementById('globalConfirmModalMethod');
        methodInput.value = config.method || 'POST';
        
        const confirmBtn = document.getElementById('globalConfirmModalConfirmBtn');
        confirmBtn.innerText = config.confirmText || 'Confirm';
        
        // Simple color mapping
        confirmBtn.className = confirmBtn.className.replace(/bg-[a-z]+-600 hover:bg-[a-z]+-700 focus:ring-[a-z]+-500/, '');
        const color = config.confirmColor || 'red';
        confirmBtn.classList.add(`bg-${color}-600`, `hover:bg-${color}-700`, `focus:ring-${color}-500`);
        
        document.getElementById('globalConfirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('globalConfirmModal').classList.add('hidden');
    }

    // Accessible ESC key closing
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !document.getElementById('globalConfirmModal').classList.contains('hidden')) {
            closeConfirmModal();
        }
    });
</script>
