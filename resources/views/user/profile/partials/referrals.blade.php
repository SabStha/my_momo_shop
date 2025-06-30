<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Referral Program</h2>
    </div>
    <div class="p-6">
        <!-- Referral Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $user->referrals()->count() }}</div>
                <div class="text-sm text-blue-800">Total Referrals</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">{{ $user->points ?? 0 }}</div>
                <div class="text-sm text-green-800">Points Earned</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $user->referral_code }}</div>
                <div class="text-sm text-purple-800">Your Code</div>
            </div>
        </div>

        <!-- Referral Link -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Your Referral Link</label>
            <div class="flex items-center space-x-2">
                <input type="text" 
                       value="{{ url('/register?ref='.$user->referral_code) }}" 
                       readonly
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm">
                <button onclick="copyToClipboard('{{ url('/register?ref='.$user->referral_code) }}')" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                    Copy
                </button>
            </div>
        </div>

        <!-- Social Sharing -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Share Your Referral Link</h3>
            <div class="flex flex-wrap gap-2">
                <button onclick="shareOnFacebook()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </button>
                <button onclick="shareOnWhatsApp()" 
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                    WhatsApp
                </button>
                <button onclick="shareOnTelegram()" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                    Telegram
                </button>
            </div>
        </div>

        <!-- How It Works -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">How It Works</h3>
            <div class="text-sm text-gray-700 space-y-2">
                <div class="flex items-start">
                    <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-3 mt-0.5">1</span>
                    <span>Share your referral link with friends and family</span>
                </div>
                <div class="flex items-start">
                    <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-3 mt-0.5">2</span>
                    <span>When they register using your link, you both get points</span>
                </div>
                <div class="flex items-start">
                    <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-3 mt-0.5">3</span>
                    <span>Use your points for discounts on future orders</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Referral link copied to clipboard!', 'success');
        }).catch(function(err) {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Referral link copied to clipboard!', 'success');
    } catch (err) {
        showToast('Failed to copy to clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

function shareOnFacebook() {
    const url = encodeURIComponent('{{ url('/register?ref='.$user->referral_code) }}');
    const text = encodeURIComponent('Join me on Ama Ko Shop! Use my referral code: {{ $user->referral_code }}');
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
}

function shareOnWhatsApp() {
    const text = encodeURIComponent('Join me on Ama Ko Shop! Use my referral code: {{ $user->referral_code }} - {{ url('/register?ref='.$user->referral_code) }}');
    window.open(`https://wa.me/?text=${text}`, '_blank');
}

function shareOnTelegram() {
    const text = encodeURIComponent('Join me on Ama Ko Shop! Use my referral code: {{ $user->referral_code }} - {{ url('/register?ref='.$user->referral_code) }}');
    window.open(`https://t.me/share/url?url={{ urlencode(url('/register?ref='.$user->referral_code)) }}&text=${text}`, '_blank');
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } shadow-lg z-50 transform transition-all duration-300 ease-in-out translate-y-0 opacity-100`;
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 2700);
}
</script> 