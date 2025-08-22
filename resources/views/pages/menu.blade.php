@extends('layouts.app')

@section('content')
<div x-data="{ 
    activeTab: 'combo',
    isLoaded: false,
    animateTab: false,
    hoveredTab: null,
    touchedCard: null
}" 
x-init="
    isLoaded = true;
    setTimeout(() => animateTab = true, 100);
" 
class="bg-[#F4E9E1] min-h-screen overflow-x-hidden">

    <!-- SECONDARY NAV BAR -->
    <div class="relative z-10 pt-[20px] pb-6 overflow-x-hidden" 
         x-show="isLoaded" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="w-full max-w-md mx-auto bg-white rounded-xl shadow-lg px-4 py-2 hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            <div class="flex gap-2 sm:gap-4 font-bold text-xs sm:text-sm text-[#000] overflow-x-auto">
                <button @click="activeTab = 'combo'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                        @mouseenter="hoveredTab = 'combo'"
                        @mouseleave="hoveredTab = null"
                        @touchstart="hoveredTab = 'combo'"
                        @touchend="setTimeout(() => hoveredTab = null, 200)"
                        :class="{ 'text-red-600 scale-110 shadow-lg': activeTab === 'combo' }" 
                        class="tab-button relative px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95 flex-shrink-0">
                    <span class="relative z-10">COMBO</span>
                    <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                         :class="{ 'scale-x-100': hoveredTab === 'combo' || activeTab === 'combo' }"></div>
                </button>
                <button @click="activeTab = 'food'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                        @mouseenter="hoveredTab = 'food'"
                        @mouseleave="hoveredTab = null"
                        @touchstart="hoveredTab = 'food'"
                        @touchend="setTimeout(() => hoveredTab = null, 200)"
                        :class="{ 'text-red-600 scale-110 shadow-lg': activeTab === 'food' }" 
                        class="tab-button relative px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95 flex-shrink-0">
                    <span class="relative z-10">FOOD</span>
                    <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                         :class="{ 'scale-x-100': hoveredTab === 'food' || activeTab === 'food' }"></div>
                </button>
                <button @click="activeTab = 'drinks'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                        @mouseenter="hoveredTab = 'drinks'"
                        @mouseleave="hoveredTab = null"
                        @touchstart="hoveredTab = 'drinks'"
                        @touchend="setTimeout(() => hoveredTab = null, 200)"
                        :class="{ 'text-red-600 scale-110 shadow-lg': activeTab === 'drinks' }" 
                        class="tab-button relative px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95 flex-shrink-0">
                    <span class="relative z-10">DRINKS</span>
                    <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                         :class="{ 'scale-x-100': hoveredTab === 'drinks' || activeTab === 'drinks' }"></div>
                </button>
                <button @click="activeTab = 'desserts'; animateTab = false; setTimeout(() => animateTab = true, 50)" 
                        @mouseenter="hoveredTab = 'desserts'"
                        @mouseleave="hoveredTab = null"
                        @touchstart="hoveredTab = 'desserts'"
                        @touchend="setTimeout(() => hoveredTab = null, 200)"
                        :class="{ 'text-red-600 scale-110 shadow-lg': activeTab === 'desserts' }" 
                        class="tab-button relative px-2 py-2 rounded-lg transition-all duration-300 transform hover:scale-105 hover:bg-red-50 hover:shadow-md active:scale-95 flex-shrink-0">
                    <span class="relative z-10">DESSERTS</span>
                    <div class="absolute inset-0 bg-red-100 rounded-lg transform scale-x-0 transition-transform duration-300 origin-left"
                         :class="{ 'scale-x-100': hoveredTab === 'desserts' || activeTab === 'desserts' }"></div>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB CONTENT AREA -->
    <div class="px-4 pb-4 space-y-8 overflow-x-hidden">
        <!-- COMBO SECTION -->
        <div x-show="activeTab === 'combo' && animateTab" 
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
             x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
            @include('menu.combo')
        </div>

        <!-- FOOD SECTION -->
        <div x-show="activeTab === 'food' && animateTab" 
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
             x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
            @include('menu.food')
        </div>

        <!-- DRINKS SECTION -->
        <div x-show="activeTab === 'drinks' && animateTab" 
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
             x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
            @include('menu.drinks')
        </div>

        <!-- DESSERTS SECTION -->
        <div x-show="activeTab === 'desserts' && animateTab" 
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
             x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-x-12 scale-95">
            @include('menu.desserts')
        </div>
    </div>
</div>

<!-- Include Cart Modal -->
@include('components.cart-modal')

<!-- Enhanced AOS animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true,
        offset: 100
    });
    
    // Custom animation for menu items
    document.addEventListener('DOMContentLoaded', function() {
        // Add staggered animation to menu items
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Enhanced hover effects for product cards (desktop)
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            // Desktop hover effects
            card.addEventListener('mouseenter', function() {
                if (window.innerWidth > 768) { // Only on desktop
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
                    
                    // Show badges on hover
                    const badge = this.querySelector('.mt-4');
                    if (badge && badge.style) {
                        badge.style.opacity = '1';
                        badge.style.transform = 'translateY(0)';
                    }
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (window.innerWidth > 768) { // Only on desktop
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                    
                    // Hide badges on leave
                    const badge = this.querySelector('.mt-4');
                    if (badge && badge.style) {
                        badge.style.opacity = '0';
                        badge.style.transform = 'translateY(4px)';
                    }
                }
            });
            
            // Mobile touch effects
            card.addEventListener('touchstart', function() {
                if (window.innerWidth <= 768) { // Only on mobile
                    this.style.transform = 'translateY(-4px) scale(1.01)';
                    this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
                    
                    // Show badges on touch
                    const badge = this.querySelector('.mt-4');
                    if (badge && badge.style) {
                        badge.style.opacity = '1';
                        badge.style.transform = 'translateY(0)';
                    }
                }
            });
            
            card.addEventListener('touchend', function() {
                if (window.innerWidth <= 768) { // Only on mobile
                    setTimeout(() => {
                        this.style.transform = 'translateY(0) scale(1)';
                        this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
                        
                        // Hide badges after touch
                        const badge = this.querySelector('.mt-4');
                        if (badge && badge.style) {
                            badge.style.opacity = '0';
                            badge.style.transform = 'translateY(4px)';
                        }
                    }, 300);
                }
            });
        });
        
        // Mobile-specific animations
        if (window.innerWidth <= 768) {
            // Add mobile-specific classes
            document.body.classList.add('mobile-device');
            
            // Enhanced touch feedback for buttons
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.95)';
                });
                
                button.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });
        }
    });
</script>

<style>
    /* Custom animations */
    @keyframes slideInFromLeft {
        0% {
            opacity: 0;
            transform: translateX(-50px) scale(0.9);
        }
        100% {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }
    
    @keyframes slideInFromRight {
        0% {
            opacity: 0;
            transform: translateX(50px) scale(0.9);
        }
        100% {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }
    
    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }
    
    @keyframes slideInFromBottom {
        0% {
            opacity: 0;
            transform: translateY(50px) scale(0.9);
        }
        100% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.3);
        }
        50% {
            opacity: 1;
            transform: scale(1.05);
        }
        70% {
            transform: scale(0.9);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes touchRipple {
        0% {
            transform: scale(0);
            opacity: 1;
        }
        100% {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .menu-item {
        animation: fadeInUp 0.8s ease-out forwards;
        transition: all 0.3s ease;
    }
    
    .menu-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .menu-item:nth-child(odd) {
        animation: slideInFromLeft 0.8s ease-out forwards;
    }
    
    .menu-item:nth-child(even) {
        animation: slideInFromRight 0.8s ease-out forwards;
    }
    
    .product-image {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    
    .product-image:hover {
        transform: scale(1.15) rotate(3deg);
        box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        filter: brightness(1.1);
    }
    
    .product-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 16px;
        overflow: hidden;
    }
    
    .product-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .tab-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .tab-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.2);
    }
    
    .tab-button::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #dc2626, #ef4444);
        transition: all 0.3s ease;
        transform: translateX(-50%);
        border-radius: 2px;
    }
    
    .tab-button:hover::after {
        width: 80%;
    }
    
    .tab-button.active::after {
        width: 100%;
    }
    
    /* Enhanced text animations */
    .animated-text {
        transition: all 0.3s ease;
    }
    
    .animated-text:hover {
        color: #dc2626;
        transform: translateX(5px);
    }
    
    /* Sliding animations for content */
    .slide-content {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .slide-content:hover {
        transform: translateX(10px);
    }
    
    /* Floating animation for images */
    .floating-image {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }
    
    /* Glow effect on hover */
    .glow-on-hover {
        transition: all 0.3s ease;
    }
    
    .glow-on-hover:hover {
        box-shadow: 0 0 20px rgba(220, 38, 38, 0.3);
        transform: scale(1.05);
    }
    
    /* Staggered animation for multiple elements */
    .stagger-animation > * {
        opacity: 0;
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .stagger-animation > *:nth-child(1) { animation-delay: 0.1s; }
    .stagger-animation > *:nth-child(2) { animation-delay: 0.2s; }
    .stagger-animation > *:nth-child(3) { animation-delay: 0.3s; }
    .stagger-animation > *:nth-child(4) { animation-delay: 0.4s; }
    .stagger-animation > *:nth-child(5) { animation-delay: 0.5s; }
    
    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .product-card {
            transition: all 0.2s ease;
        }
        
        .product-card:active {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .product-image {
            transition: all 0.2s ease;
        }
        
        .product-image:active {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .tab-button {
            transition: all 0.2s ease;
        }
        
        .tab-button:active {
            transform: scale(0.95);
            background-color: rgba(220, 38, 38, 0.1);
        }
        
        .animated-text:active {
            color: #dc2626;
            transform: translateX(2px);
        }
        
        .slide-content:active {
            transform: translateX(5px);
        }
        
        .glow-on-hover:active {
            box-shadow: 0 0 15px rgba(220, 38, 38, 0.3);
            transform: scale(1.02);
        }
        
        /* Mobile touch ripple effect */
        .touch-ripple {
            position: relative;
            overflow: hidden;
        }
        
        .touch-ripple::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(220, 38, 38, 0.3);
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }
        
        .touch-ripple:active::before {
            width: 100px;
            height: 100px;
            animation: touchRipple 0.3s ease-out;
        }
    }
    
    /* Disable hover effects on mobile */
    @media (hover: none) and (pointer: coarse) {
        .product-card:hover {
            transform: none;
            box-shadow: none;
        }
        
        .product-image:hover {
            transform: none;
            box-shadow: none;
            filter: none;
        }
        
        .tab-button:hover {
            transform: none;
            box-shadow: none;
        }
        
        .animated-text:hover {
            color: inherit;
            transform: none;
        }
        
        .slide-content:hover {
            transform: none;
        }
        
        .glow-on-hover:hover {
            box-shadow: none;
            transform: none;
        }
    }
</style>

@endsection
