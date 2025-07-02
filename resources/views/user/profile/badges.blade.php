<h3 class="mb-4 text-2xl font-bold text-gray-800">Your Badges</h3>
@if($badges->isEmpty())
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
        <div class="text-6xl mb-4">🏆</div>
        <h4 class="text-lg font-semibold text-blue-800 mb-2">No Badges Yet</h4>
        <p class="text-blue-600">Start ordering and engaging to unlock amazing badges!</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($badges as $badge)
            @php
                // Map rank colors to Tailwind classes with fallbacks
                $rankColors = [
                    'bronze' => ['bg' => 'bg-amber-600', 'text' => 'text-amber-800', 'border' => 'border-amber-400', 'gradient' => 'from-amber-400 to-amber-600'],
                    'silver' => ['bg' => 'bg-gray-400', 'text' => 'text-gray-800', 'border' => 'border-gray-300', 'gradient' => 'from-gray-300 to-gray-500'],
                    'gold' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-800', 'border' => 'border-yellow-400', 'gradient' => 'from-yellow-400 to-yellow-600'],
                    'elite' => ['bg' => 'bg-purple-600', 'text' => 'text-purple-800', 'border' => 'border-purple-400', 'gradient' => 'from-purple-400 to-purple-600']
                ];
                
                $rankCode = strtolower($badge->badgeRank->code);
                $colors = $rankColors[$rankCode] ?? $rankColors['bronze'];
                
                // Get badge progress for this user and badge class
                $progress = auth()->user()->badgeProgress()->where('badge_class_id', $badge->badgeClass->id)->first();
                $nextTier = $badge->badgeTier->getNextTier();
                $nextRank = $badge->badgeRank->getNextRank();
                
                // Get current benefits
                $currentBenefits = $badge->badgeClass->benefits ?? [];
                $tierBenefits = $badge->badgeTier->benefits ?? [];
                $allBenefits = array_merge($currentBenefits, $tierBenefits);
                
                // Get next tier benefits
                $nextTierBenefits = $nextTier ? ($nextTier->benefits ?? []) : [];
                $nextRankBenefits = $nextRank ? ($nextRank->benefits ?? []) : [];
                $nextBenefits = array_merge($nextTierBenefits, $nextRankBenefits);
            @endphp
            
            <div class="group relative">
                <!-- Badge Container -->
                <div class="relative bg-gradient-to-br {{ $colors['gradient'] }} rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-2 {{ $colors['border'] }} overflow-hidden">
                    
                    <!-- Shine Effect -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 pointer-events-none"></div>
                    
                    <!-- Badge Icon -->
                    <div class="text-center mb-4 relative z-10">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full shadow-lg mb-3 border-2 border-white/30">
                            <span class="text-3xl">{{ $badge->badgeClass->icon }}</span>
                        </div>
                    </div>
                    
                    <!-- Badge Title -->
                    <div class="text-center mb-4 relative z-10">
                        <h4 class="text-lg font-bold text-white mb-2 drop-shadow-sm">
                            {{ $badge->badgeClass->name }}
                        </h4>
                        <div class="flex items-center justify-center gap-2 flex-wrap">
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-sm font-semibold rounded-full border border-white/30">
                                {{ $badge->badgeRank->name }}
                            </span>
                            <span class="px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-sm font-semibold rounded-full border border-white/30">
                                {{ $badge->badgeTier->name }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    <div class="text-center mb-4 relative z-10">
                        @if($badge->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-500/20 backdrop-blur-sm text-white border border-green-400/30">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                Active
                            </span>
                        @elseif($badge->status === 'inactive')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-500/20 backdrop-blur-sm text-white border border-yellow-400/30">
                                <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                                Inactive
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-500/20 backdrop-blur-sm text-white border border-red-400/30">
                                <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                                Expired
                            </span>
                        @endif
                    </div>
                    
                    <!-- Current Benefits -->
                    @if(!empty($allBenefits))
                        <div class="mb-4 relative z-10">
                            <h5 class="text-sm font-semibold text-white mb-2 flex items-center">
                                <span class="mr-2">🎁</span>
                                Current Benefits
                            </h5>
                            <div class="space-y-1">
                                @foreach(array_slice($allBenefits, 0, 3) as $benefit)
                                    <div class="text-xs text-white/90 flex items-start">
                                        <span class="text-white mr-1">•</span>
                                        <span>{{ $benefit['description'] ?? $benefit }}</span>
                                    </div>
                                @endforeach
                                @if(count($allBenefits) > 3)
                                    <div class="text-xs text-white/70 italic">
                                        +{{ count($allBenefits) - 3 }} more benefits
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Progress to Next Tier -->
                    @if($progress && $nextTier)
                        <div class="mb-4 relative z-10">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs text-white/80">Progress to {{ $nextTier->name }}</span>
                                <span class="text-xs text-white font-semibold">{{ $progress->current_points }}/{{ $nextTier->points_required }}</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                @php
                                    $progressPercent = min(100, ($progress->current_points / $nextTier->points_required) * 100);
                                @endphp
                                <div class="bg-white h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                            </div>
                            <div class="text-xs text-white/70 mt-1">
                                {{ $nextTier->points_required - $progress->current_points }} more points needed
                            </div>
                        </div>
                    @endif
                    
                    <!-- Next Tier Benefits Preview -->
                    @if(!empty($nextBenefits) && $nextTier)
                        <div class="mb-4 relative z-10">
                            <h5 class="text-sm font-semibold text-white mb-2 flex items-center">
                                <span class="mr-2">⭐</span>
                                Next Tier Benefits
                            </h5>
                            <div class="space-y-1">
                                @foreach(array_slice($nextBenefits, 0, 2) as $benefit)
                                    <div class="text-xs text-white/80 flex items-start">
                                        <span class="text-white/60 mr-1">•</span>
                                        <span>{{ $benefit['description'] ?? $benefit }}</span>
                                    </div>
                                @endforeach
                                @if(count($nextBenefits) > 2)
                                    <div class="text-xs text-white/60 italic">
                                        +{{ count($nextBenefits) - 2 }} more benefits
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                    <!-- Badge Details -->
                    <div class="space-y-2 text-sm relative z-10">
                        <div class="flex items-center justify-between">
                            <span class="text-white/80 font-medium">Earned:</span>
                            <span class="text-white font-semibold">{{ $badge->earned_at->format('M d, Y') }}</span>
                        </div>
                        @if($badge->expires_at)
                            <div class="flex items-center justify-between">
                                <span class="text-white/80 font-medium">Expires:</span>
                                <span class="text-white font-semibold">{{ $badge->expires_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Earned Info -->
                    <div class="mt-4 p-3 bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 relative z-10">
                        <p class="text-xs text-white/90 leading-relaxed">
                            <span class="font-semibold">How earned:</span><br>
                            {{ $badge->earned_data_text }}
                        </p>
                    </div>
                    
                    <!-- Badge Ribbon Effect -->
                    <div class="absolute top-0 right-0 w-0 h-0 border-l-[30px] border-l-transparent border-t-[30px] border-t-white/20 transform rotate-45 translate-x-2 -translate-y-2"></div>
                </div>
            </div>
        @endforeach
    </div>
@endif 