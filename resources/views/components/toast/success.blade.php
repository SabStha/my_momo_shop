@if(session()->has('success') || session()->has('message'))
    <div id="globalSuccessToast" class="fixed top-5 right-5 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden z-50 transition-all duration-300 transform translate-x-0">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900" id="globalSuccessToastMessage">
                        {{ session('success') ?? session('message') }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeSuccessToast()">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@else
    <div id="globalSuccessToast" class="hidden fixed top-5 right-5 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden z-50 transition-all duration-300 transform translate-x-full">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium text-gray-900" id="globalSuccessToastMessage"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="closeSuccessToast()">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    function showSuccessToast(message) {
        const toast = document.getElementById('globalSuccessToast');
        document.getElementById('globalSuccessToastMessage').innerText = message;
        toast.classList.remove('hidden', 'translate-x-full');
        toast.classList.add('translate-x-0');
        
        setTimeout(closeSuccessToast, 4000); // Auto-dismiss after 4s
    }
    
    function closeSuccessToast() {
        const toast = document.getElementById('globalSuccessToast');
        if(!toast) return;
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }

    // Attempt to dismiss if it originated from session flash
    @if(session()->has('success') || session()->has('message'))
        setTimeout(closeSuccessToast, 4000);
    @endif
</script>
