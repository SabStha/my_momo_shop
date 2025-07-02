{{-- User Profile Content --}}
@include('user.profile.badges', ['badges' => $user->userBadges()->with(['badgeClass', 'badgeRank', 'badgeTier'])->active()->get()]) 