<div class="p-4 space-y-8">
  <!-- Hero Header Section -->
  <div class="bg-gradient-to-r from-[#FFF9F0] to-[#FDF6E3] rounded-3xl p-8 shadow-xl relative overflow-hidden">
    <div class="absolute inset-0 bg-black/5"></div>
    <div class="relative z-10 text-center">
      <!-- Sparkle Animation Behind Trophy -->
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-32 h-32 bg-gradient-to-r from-yellow-200/30 to-orange-200/30 rounded-full animate-pulse"></div>
      </div>
      <div class="text-6xl mb-4 relative z-20">🏆</div>
      <h2 class="text-3xl font-serif font-bold text-[#7A1C1E] mb-2 tracking-wide drop-shadow-sm">Your Achievement Collection</h2>
      <p class="text-[#A43E2D]/80 text-lg">Unlock badges, climb ranks, and become a Momo legend!</p>
    </div>
    <!-- Subtle Floating Elements -->
    <div class="absolute top-4 left-4 text-2xl animate-bounce opacity-60">🥇</div>
    <div class="absolute top-8 right-8 text-xl animate-pulse opacity-60">⭐</div>
    <div class="absolute bottom-6 left-12 text-lg animate-ping opacity-60">🎖️</div>
  </div>

  <!-- Stats Dashboard -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="bg-white rounded-xl shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 hover:scale-105">
      <div class="text-3xl mb-2">🏆</div>
      <div class="text-2xl font-bold text-[#7A1C1E]">{{ $badges->count() }}</div>
      <div class="text-sm text-gray-600">Badges Earned</div>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 hover:scale-105">
      <div class="text-3xl mb-2">👑</div>
      <div class="text-xl font-bold text-[#7A1C1E]">
        @php
            $highestBadge = $badges->sortByDesc('badgeRank.level')->first();
            echo $highestBadge ? $highestBadge->badgeRank->name : 'None';
        @endphp
      </div>
      <div class="text-sm text-gray-600">Highest Rank</div>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 hover:scale-105">
      <div class="text-3xl mb-2">💰</div>
      <div class="text-xl font-bold text-[#7A1C1E]">
        @php
            $totalCredits = $badges->sum(function($badge) {
                $baseCredits = 100;
                $rankMultiplier = $badge->badgeRank->level;
                $tierMultiplier = $badge->badgeTier->level;
                return $baseCredits * $rankMultiplier * $tierMultiplier;
            });
            echo number_format($totalCredits);
        @endphp
      </div>
      <div class="text-sm text-gray-600">Credits Won</div>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-4 text-center hover:shadow-lg transition-all duration-300 hover:scale-105">
      <div class="text-3xl mb-2">🎯</div>
      <div class="text-lg font-bold text-[#7A1C1E]">
        @php
            $userProgress = auth()->user()->badgeProgress()->with('badgeClass')->get();
            $lowestProgress = $userProgress->sortBy('progress_percentage')->first();
            echo $lowestProgress ? $lowestProgress->badgeClass->name : 'No Focus';
        @endphp
      </div>
      <div class="text-sm text-gray-600">Current Quest</div>
    </div>
  </div>

  <!-- Progress Overview -->
  <div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-serif font-semibold text-[#7A1C1E]">Collection Progress</h3>
      <div class="text-sm text-gray-600">{{ $badges->count() }} of 9 badges collected</div>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
      <div class="bg-gradient-to-r from-[#7A1C1E] to-[#A43E2D] h-3 rounded-full transition-all duration-500" 
           style="width: {{ ($badges->count() / 9) * 100 }}%"></div>
    </div>
  </div>

  <!-- Badge Collection Gallery -->
  <div class="space-y-6">
    <h3 class="text-2xl font-serif font-semibold text-[#7A1C1E]">🎮 Badge Collection</h3>
    
    @php
        $badgeClasses = \App\Models\BadgeClass::active()->with(['ranks.tiers'])->get();
        $userProgress = auth()->user()->badgeProgress()->with('badgeClass')->get();
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($badgeClasses as $badgeClass)
        @php
            $progress = $userProgress->where('badge_class_id', $badgeClass->id)->first();
            $currentRank = $progress ? $progress->getCurrentRank() : null;
            $currentTier = $progress ? $progress->getCurrentTier() : null;
            $nextTier = $progress ? $progress->getNextTier() : null;
            $userHasBadge = $badges->where('badge_class_id', $badgeClass->id)->isNotEmpty();
            $isLocked = !$userHasBadge && $badgeClass->code === 'gold_plus';
            
            // Badge visual states with muted colors
            $badgeState = $userHasBadge ? 'unlocked' : ($isLocked ? 'locked' : 'available');
            $badgeColors = [
                'gold_plus' => ['bg' => 'from-red-700 to-red-800', 'border' => 'border-red-600', 'text' => 'text-red-700'],
                'loyalty' => ['bg' => 'from-blue-700 to-blue-800', 'border' => 'border-blue-600', 'text' => 'text-blue-700'],
                'engagement' => ['bg' => 'from-green-700 to-green-800', 'border' => 'border-green-600', 'text' => 'text-green-700']
            ];
            $colors = $badgeColors[$badgeClass->code] ?? $badgeColors['loyalty'];
        @endphp
        
        <div class="relative group">
          <!-- Badge Card -->
          <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105 border border-gray-200 relative overflow-hidden {{ $badgeState === 'locked' ? 'opacity-60 grayscale' : '' }}">
            
            <!-- Subtle Shine Effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 pointer-events-none"></div>
                    
                    <!-- Badge Icon -->
            <div class="text-center mb-4">
              <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br {{ $colors['bg'] }} rounded-full shadow-lg mb-4 border-2 {{ $colors['border'] }}">
                <span class="text-4xl text-white">{{ $badgeClass->icon }}</span>
              </div>
              
              <!-- Badge Status -->
              @if($badgeState === 'unlocked')
                <div class="absolute top-4 right-4">
                  <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center shadow-md">
                    <span class="text-white text-xs">✓</span>
                  </div>
                </div>
              @elseif($badgeState === 'locked')
                <div class="absolute top-4 right-4">
                  <div class="w-6 h-6 bg-gray-500 rounded-full flex items-center justify-center shadow-md">
                    <span class="text-white text-xs">🔒</span>
                        </div>
                    </div>
              @endif
            </div>
            
            <!-- Badge Info -->
            <div class="text-center">
              <h4 class="text-xl font-bold text-[#7A1C1E] mb-2">{{ $badgeClass->name }}</h4>
              
              @if($currentRank)
                <div class="flex items-center justify-center space-x-2 mb-3">
                  <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full border border-gray-200">
                    {{ $currentRank->name }} Rank
                            </span>
                  <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-semibold rounded-full border border-gray-200">
                    Tier {{ $currentTier ? $currentTier->level : '0' }}
                            </span>
                        </div>
              @else
                <div class="text-sm text-gray-500 mb-3">No rank yet</div>
              @endif
              
              <!-- Progress Bar -->
              @if($progress && $currentTier)
                <div class="mb-4">
                  <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">Progress</span>
                    <span class="text-[#7A1C1E] font-semibold">{{ $progress->current_points }} pts</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-[#7A1C1E] to-[#A43E2D] h-2 rounded-full transition-all duration-500" 
                         style="width: {{ min(100, ($progress->current_points / 1000) * 100) }}%"></div>
                  </div>
                  @if($nextTier)
                    <div class="text-xs text-gray-500 mt-1">
                      {{ $nextTier->points_required - $progress->current_points }} pts to next tier
                    </div>
                  @endif
                </div>
              @endif
              
              <!-- Benefits Preview -->
              @if($currentTier && $currentTier->benefits)
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                  <p class="text-xs font-semibold text-[#7A1C1E] mb-2">🎁 Current Rewards:</p>
                  <ul class="text-xs space-y-1 text-gray-600">
                    @foreach(array_slice($currentTier->benefits, 0, 2) as $benefit)
                      <li class="flex items-start">
                        <span class="text-[#A43E2D] mr-1">•</span>
                        <span>{{ $benefit['description'] ?? $benefit }}</span>
                      </li>
                    @endforeach
                  </ul>
                </div>
              @endif
                    </div>
                    
            <!-- Action Button -->
            <div class="text-center mt-4">
              @if($badgeState === 'unlocked')
                <button onclick="showBadgeDetails('{{ $badgeClass->name }}', '{{ $currentRank ? $currentRank->name : 'None' }}', '{{ $currentTier ? $currentTier->name : 'None' }}')" class="bg-[#A43E2D] text-white px-6 py-2 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300 shadow-md hover:shadow-lg">
                  View Details
                </button>
              @elseif($badgeState === 'locked')
                <button onclick="showBadgeRequirements('{{ $badgeClass->name }}')" class="bg-gray-400 text-white px-6 py-2 rounded-full font-semibold transition-all duration-300 shadow-md cursor-not-allowed">
                  Locked
                </button>
                        @else
                <a href="{{ route('menu') }}" class="bg-[#A43E2D] text-white px-6 py-2 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300 shadow-md hover:shadow-lg inline-block">
                  Start Quest
                </a>
                        @endif
                    </div>
                    
            <!-- Achievement Ribbon -->
            @if($badgeState === 'unlocked')
              <div class="absolute -top-2 -right-2 w-0 h-0 border-l-[20px] border-l-transparent border-t-[20px] border-t-yellow-400 transform rotate-45"></div>
            @endif
          </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

  <!-- Gold+ Elite Section -->
  @php
      $canApplyForGoldPlus = auth()->user()->canApplyForGoldPlus();
      $loyaltyBadge = auth()->user()->getHighestBadge('loyalty');
      $engagementBadge = auth()->user()->getHighestBadge('engagement');
      $loyaltyTier = $loyaltyBadge ? $loyaltyBadge->badgeTier->level : 0;
      $engagementTier = $engagementBadge ? $engagementBadge->badgeTier->level : 0;
      $tiersNeeded = (3 - $loyaltyTier) + (3 - $engagementTier);
                                @endphp
  
  @if($canApplyForGoldPlus)
    <div class="bg-gradient-to-br from-yellow-100 to-orange-100 rounded-3xl p-8 shadow-xl relative overflow-hidden backdrop-blur-sm">
      <div class="absolute inset-0 bg-black/5"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">🏆 Elite Achievement Unlocked!</h4>
            <p class="text-[#A43E2D]/80 text-lg mb-4">You've reached the pinnacle of Momo loyalty!</p>
            <div class="space-y-3">
              <div class="flex items-center">
                <span class="text-green-500 mr-3 text-xl">✓</span>
                <div class="flex-1">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">Loyalty: Tier {{ $loyaltyTier }}/3</span>
                    <span class="text-[#7A1C1E] font-semibold">100%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                  </div>
                </div>
              </div>
              <div class="flex items-center">
                <span class="text-green-500 mr-3 text-xl">✓</span>
                <div class="flex-1">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">Engagement: Tier {{ $engagementTier }}/3</span>
                    <span class="text-[#7A1C1E] font-semibold">100%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <button onclick="showGoldPlusApplication()" class="bg-[#A43E2D] text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-[#7A1C1E] transition-all duration-300 shadow-lg hover:shadow-xl">
            🚀 Apply for Gold+
          </button>
        </div>
      </div>
    </div>
  @else
    <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl p-8 shadow-xl relative overflow-hidden backdrop-blur-sm">
      <div class="absolute inset-0 bg-black/10"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">🔒 Elite Achievement Locked</h4>
            <p class="text-gray-600 text-lg mb-4">You're {{ $tiersNeeded }} tier{{ $tiersNeeded !== 1 ? 's' : '' }} away from elite status!</p>
            <div class="space-y-3">
              <div class="flex items-center">
                <span class="text-gray-400 mr-3 text-xl">📍</span>
                <div class="flex-1">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">Loyalty: Tier {{ $loyaltyTier }}/3</span>
                    <span class="text-[#7A1C1E] font-semibold">{{ ($loyaltyTier / 3) * 100 }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-[#7A1C1E] h-2 rounded-full" style="width: {{ ($loyaltyTier / 3) * 100 }}%"></div>
                  </div>
                </div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-400 mr-3 text-xl">📍</span>
                <div class="flex-1">
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-700">Engagement: Tier {{ $engagementTier }}/3</span>
                    <span class="text-[#7A1C1E] font-semibold">{{ ($engagementTier / 3) * 100 }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-[#7A1C1E] h-2 rounded-full" style="width: {{ ($engagementTier / 3) * 100 }}%"></div>
                  </div>
                            </div>
                            </div>
                        </div>
                                    </div>
          <button onclick="showGoldPlusBenefits()" class="bg-[#A43E2D] text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-[#7A1C1E] transition-all duration-300 shadow-lg hover:shadow-xl">
            🎯 See Benefits
          </button>
                                    </div>
                            </div>
                        </div>
                    @endif
                    
  <!-- Achievement Timeline -->
  @if($badges->isNotEmpty())
    <div class="space-y-6">
      <h3 class="text-2xl font-serif font-semibold text-[#7A1C1E]">📜 Achievement History</h3>
      <div class="space-y-4">
        @foreach($badges->sortByDesc('earned_at') as $badge)
          <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border border-gray-100">
                        <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-br from-{{ $badge->badgeRank->code === 'gold' ? 'yellow' : ($badge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-400 to-{{ $badge->badgeRank->code === 'gold' ? 'yellow' : ($badge->badgeRank->code === 'silver' ? 'gray' : 'amber') }}-600 rounded-full flex items-center justify-center shadow-lg">
                  <span class="text-2xl text-white">{{ $badge->badgeClass->icon }}</span>
                </div>
                <div>
                  <h4 class="text-xl font-bold text-[#7A1C1E]">{{ $badge->badgeClass->name }}</h4>
                  <p class="text-gray-600">{{ $badge->badgeRank->name }} Rank • {{ $badge->badgeTier->name }}</p>
                  <p class="text-sm text-gray-500">{{ $badge->earned_data_text }}</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-500">{{ $badge->earned_at->format('M d, Y') }}</div>
                <div class="text-xs text-green-600 font-semibold">ACHIEVED!</div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
                        </div>
  @else
    <!-- Empty State -->
    <div class="text-center py-16">
      <div class="text-8xl mb-6 animate-bounce">🏆</div>
      <h3 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-4">Start Your Quest!</h3>
      <p class="text-gray-600 text-lg mb-6">Your badge collection awaits. Order, engage, and unlock achievements!</p>
      <a href="{{ route('menu') }}" class="bg-[#A43E2D] text-white px-8 py-4 rounded-full inline-block font-bold text-lg hover:bg-[#7A1C1E] transition-all duration-300 shadow-lg hover:shadow-xl">
        🚀 Begin Your Journey
      </a>
                            </div>
                        @endif
                    </div>
                    
<script>
// Badge detail modal function
function showBadgeDetails(badgeName, rank, tier) {
  const modal = `
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl">
        <div class="text-center mb-6">
          <div class="text-6xl mb-4">🏆</div>
          <h3 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">${badgeName}</h3>
          <p class="text-gray-600">Congratulations on earning this badge!</p>
        </div>
        
        <div class="space-y-4 mb-6">
          <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <span class="text-gray-700">Rank:</span>
            <span class="font-semibold text-[#7A1C1E]">${rank}</span>
          </div>
          <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <span class="text-gray-700">Tier:</span>
            <span class="font-semibold text-[#7A1C1E]">${tier}</span>
          </div>
        </div>
        
        <div class="text-center">
          <button onclick="closeModal()" class="bg-[#A43E2D] text-white px-6 py-3 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300">
            Close
          </button>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modal);
}

// Badge requirements modal function
function showBadgeRequirements(badgeName) {
  const requirements = {
    'Momo Loyalty': 'Order food regularly to earn loyalty points. Each order contributes to your loyalty tier progression.',
    'Momo Engagement': 'Try different menu items, share on social media, and participate in community activities.',
    'AmaKo Gold+': 'Reach Tier 3 in both Loyalty and Engagement badges to unlock this exclusive elite badge.'
  };
  
  const requirement = requirements[badgeName] || 'Complete specific requirements to unlock this badge.';
  
  const modal = `
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl">
        <div class="text-center mb-6">
          <div class="text-6xl mb-4">🔒</div>
          <h3 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">${badgeName}</h3>
          <p class="text-gray-600">This badge is currently locked</p>
        </div>
        
        <div class="mb-6">
          <h4 class="font-semibold text-[#7A1C1E] mb-3">Requirements:</h4>
          <p class="text-gray-700 leading-relaxed">${requirement}</p>
        </div>
        
        <div class="text-center space-y-3">
          <a href="{{ route('menu') }}" class="bg-[#A43E2D] text-white px-6 py-3 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300 inline-block">
            🚀 Start Earning
          </a>
          <button onclick="closeModal()" class="block w-full text-gray-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition-all duration-300">
            Close
          </button>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modal);
}

// Close modal function
function closeModal() {
  const modals = document.querySelectorAll('.fixed.inset-0');
  modals.forEach(modal => modal.remove());
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
    closeModal();
  }
});

// Gold+ Application modal function
function showGoldPlusApplication() {
  const modal = `
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-lg mx-4 shadow-2xl">
        <div class="text-center mb-6">
          <div class="text-6xl mb-4">👑</div>
          <h3 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">Gold+ Application</h3>
          <p class="text-gray-600">You're eligible to apply for our exclusive elite badge!</p>
        </div>
        
        <div class="space-y-4 mb-6">
          <div class="bg-gradient-to-r from-yellow-100 to-orange-100 p-4 rounded-lg">
            <h4 class="font-semibold text-[#7A1C1E] mb-2">🎉 Congratulations!</h4>
            <p class="text-sm text-gray-700">You've reached Tier 3 in both Loyalty and Engagement badges, making you eligible for the exclusive AmaKo Gold+ badge.</p>
                    </div>
                    
          <div class="space-y-3">
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
              <span class="text-green-500 mr-3">✓</span>
              <span class="text-sm text-gray-700">Loyalty: Tier 3/3</span>
                </div>
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
              <span class="text-green-500 mr-3">✓</span>
              <span class="text-sm text-gray-700">Engagement: Tier 3/3</span>
            </div>
          </div>
        </div>
        
        <div class="text-center space-y-3">
          <button onclick="submitGoldPlusApplication()" class="bg-[#A43E2D] text-white px-8 py-3 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300">
            🚀 Submit Application
          </button>
          <button onclick="closeModal()" class="block w-full text-gray-600 px-6 py-3 rounded-full font-semibold hover:bg-gray-100 transition-all duration-300">
            Cancel
          </button>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modal);
}

// Gold+ Benefits modal function
function showGoldPlusBenefits() {
  const modal = `
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-lg mx-4 shadow-2xl">
        <div class="text-center mb-6">
          <div class="text-6xl mb-4">👑</div>
          <h3 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">Gold+ Benefits</h3>
          <p class="text-gray-600">Exclusive perks for elite members</p>
        </div>
        
        <div class="space-y-4 mb-6">
          <div class="bg-gradient-to-r from-yellow-100 to-orange-100 p-4 rounded-lg">
            <h4 class="font-semibold text-[#7A1C1E] mb-3">🌟 Elite Benefits</h4>
            <ul class="space-y-2 text-sm text-gray-700">
              <li class="flex items-start">
                <span class="text-yellow-500 mr-2">•</span>
                <span>Priority customer support</span>
              </li>
              <li class="flex items-start">
                <span class="text-yellow-500 mr-2">•</span>
                <span>Exclusive menu items</span>
              </li>
              <li class="flex items-start">
                <span class="text-yellow-500 mr-2">•</span>
                <span>Double loyalty points</span>
              </li>
              <li class="flex items-start">
                <span class="text-yellow-500 mr-2">•</span>
                <span>Free delivery on all orders</span>
              </li>
              <li class="flex items-start">
                <span class="text-yellow-500 mr-2">•</span>
                <span>Early access to new features</span>
              </li>
            </ul>
          </div>
          
          <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-semibold text-[#7A1C1E] mb-2">📈 Progress to Unlock</h4>
            <p class="text-sm text-gray-700">You're {{ $tiersNeeded }} tier{{ $tiersNeeded !== 1 ? 's' : '' }} away from unlocking these exclusive benefits!</p>
          </div>
        </div>
        
        <div class="text-center">
          <a href="{{ route('menu') }}" class="bg-[#A43E2D] text-white px-8 py-3 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300 inline-block">
            🚀 Start Earning
          </a>
        </div>
      </div>
    </div>
  `;
  
  document.body.insertAdjacentHTML('beforeend', modal);
}

// Submit Gold+ application function
function submitGoldPlusApplication() {
  // Show success message
  const successModal = `
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl text-center">
        <div class="text-6xl mb-4">🎉</div>
        <h3 class="text-2xl font-serif font-bold text-[#7A1C1E] mb-2">Application Submitted!</h3>
        <p class="text-gray-600 mb-6">Your Gold+ application has been submitted successfully. We'll review your eligibility and get back to you soon.</p>
        <button onclick="closeModal()" class="bg-[#A43E2D] text-white px-6 py-3 rounded-full font-semibold hover:bg-[#7A1C1E] transition-all duration-300">
          Close
        </button>
      </div>
    </div>
  `;
  
  closeModal();
  document.body.insertAdjacentHTML('beforeend', successModal);
}
</script> 