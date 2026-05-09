<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity detail-modal-backdrop" aria-hidden="true" onclick="closeDetailModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 relative">
                <!-- Close Button -->
                <button type="button" onclick="closeDetailModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 focus:outline-none z-10">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times text-xl"></i>
                </button>
                
                <div id="detailModalContent" class="mt-2 text-left">
                    <!-- Loading State -->
                    <div id="detailModalLoading" class="flex justify-center items-center py-12">
                        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-3 text-gray-600">Loading details...</span>
                    </div>
                    
                    <!-- Content will be injected here -->
                    <div id="detailModalBody" class="hidden"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openDetailModal(url) {
        document.getElementById('detailModal').classList.remove('hidden');
        document.getElementById('detailModalLoading').classList.remove('hidden');
        document.getElementById('detailModalBody').classList.add('hidden');
        document.getElementById('detailModalBody').innerHTML = '';
        
        // Lock body scroll
        document.body.style.overflow = 'hidden';
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.text();
        })
        .then(html => {
            document.getElementById('detailModalLoading').classList.add('hidden');
            document.getElementById('detailModalBody').innerHTML = html;
            document.getElementById('detailModalBody').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error fetching details:', error);
            document.getElementById('detailModalLoading').classList.add('hidden');
            document.getElementById('detailModalBody').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                    <p class="text-gray-600">Failed to load details. Please try again.</p>
                </div>
            `;
            document.getElementById('detailModalBody').classList.remove('hidden');
        });
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
        document.getElementById('detailModalBody').innerHTML = '';
        // Unlock body scroll
        document.body.style.overflow = '';
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDetailModal();
        }
    });
</script>
