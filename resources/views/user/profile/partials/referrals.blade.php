<!-- Referrals -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Referrals</h2>
        <p class="text-sm text-gray-600 mt-1">Share your referral code and earn rewards</p>
    </div>
    <div class="p-6">
        @php
            $user = auth()->user();
            $referralCode = $user->referral_code ?? strtoupper(substr(md5($user->id . $user->email), 0, 8));
            $referrals = $user->referrals ?? collect();
            $referralCount = $referrals->count();
        @endphp
        
        <!-- Referral Code Section -->
        <div class="mb-6">
            <h3 class="text-md font-medium text-gray-900 mb-3">Your Referral Code</h3>
            <div class="flex items-center space-x-3">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" 
                               value="{{ $referralCode }}" 
                               id="referralCode"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-900 font-mono text-sm"
                               readonly>
                        <button onclick="copyReferralCode()" 
                                class="absolute inset-y-0 right-0 px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-800 focus:outline-none">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Share this code with friends to earn rewards when they sign up and make their first order.</p>
        </div>
        
        <!-- Referral Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $referralCount }}</div>
                <div class="text-sm text-blue-800">Total Referrals</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $referrals->where('has_ordered', true)->count() }}</div>
                <div class="text-sm text-green-800">Successful Referrals</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">Rs {{ number_format($user->referral_earnings ?? 0, 2) }}</div>
                <div class="text-sm text-purple-800">Total Earnings</div>
            </div>
        </div>
        
        <!-- Share Options -->
        <div class="mb-6">
            <h3 class="text-md font-medium text-gray-900 mb-3">Share Your Code</h3>
            <div class="flex flex-wrap gap-2">
                <button onclick="shareViaWhatsApp()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                    WhatsApp
                </button>
                <button onclick="shareViaEmail()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email
                </button>
                <button onclick="shareViaSMS()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    SMS
                </button>
            </div>
        </div>
        
        <!-- Referral List -->
        @if($referralCount > 0)
            <div>
                <h3 class="text-md font-medium text-gray-900 mb-3">Your Referrals</h3>
                <div class="space-y-3">
                    @foreach($referrals->take(5) as $referral)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600">{{ substr($referral->name ?? 'U', 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $referral->name ?? 'User' }}</p>
                                    <p class="text-xs text-gray-500">{{ $referral->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($referral->has_ordered) bg-green-100 text-green-800 @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $referral->has_ordered ? 'Ordered' : 'Pending' }}
                            </span>
                        </div>
                    @endforeach
                    @if($referralCount > 5)
                        <p class="text-sm text-gray-500 text-center">+{{ $referralCount - 5 }} more referrals</p>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No referrals yet</h3>
                <p class="mt-1 text-sm text-gray-500">Share your referral code to start earning rewards!</p>
            </div>
        @endif
        
        <!-- Referral Terms -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-900 mb-2">How it works</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Share your unique referral code with friends</li>
                <li>• When they sign up using your code, you both get rewards</li>
                <li>• Rewards are credited after their first order</li>
                <li>• You can track your referrals and earnings here</li>
            </ul>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    const referralCode = document.getElementById('referralCode');
    referralCode.select();
    referralCode.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    button.classList.add('bg-green-600');
    button.classList.remove('bg-blue-600');
    
    setTimeout(() => {
        button.textContent = originalText;
        button.classList.remove('bg-green-600');
        button.classList.add('bg-blue-600');
    }, 2000);
}

function shareViaWhatsApp() {
    const referralCode = document.getElementById('referralCode').value;
    const message = `Hey! Use my referral code ${referralCode} to sign up for Ama Ko Momo and get special rewards! 🍜`;
    const url = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}

function shareViaEmail() {
    const referralCode = document.getElementById('referralCode').value;
    const subject = 'Join Ama Ko Momo with my referral code!';
    const body = `Hi there!

I'd love for you to try Ama Ko Momo! Use my referral code ${referralCode} when you sign up and we both get special rewards.

Check them out at: ${window.location.origin}

Best regards!`;
    
    const url = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = url;
}

function shareViaSMS() {
    const referralCode = document.getElementById('referralCode').value;
    const message = `Hey! Use my referral code ${referralCode} to sign up for Ama Ko Momo and get special rewards! 🍜`;
    const url = `sms:?body=${encodeURIComponent(message)}`;
    window.location.href = url;
}
</script> 