@php($hideTopNav = true)

@extends('layouts.app')

@section('content')
<style>
    :root {
        --momo-green: #b8d8ba;
        --momo-sand: #d9dbbc;
        --momo-cream: #fcddbc;
        --momo-pink: #ef959d;
        --momo-mocha: #69585f;
        --brand-color: #6E0D25;
        --highlight-color: #FFFFB3;
        --bottom-nav-height: 65px;
    }

    body {
        background-color: var(--momo-sand);
        color: var(--momo-mocha);
        font-family: 'Black Chancery', cursive;
    }

    .menu-tabs {
        display: flex;
        justify-content: space-around;
        background-color: var(--momo-cream);
        padding: 0.75rem 0;
        border-bottom: 2px solid var(--momo-mocha);
        overflow-x: auto;
    }

    .menu-tab {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 3px solid transparent;
        color: var(--momo-mocha);
        transition: all 0.2s ease-in-out;
    }

    .menu-tab.active {
        border-bottom: 3px solid var(--momo-pink);
        color: var(--momo-pink);
        background-color: var(--momo-green);
    }

    .menu-content {
        min-height: 60vh;
        padding: 1rem;
        background-color: var(--momo-cream);
        color: var(--momo-mocha);
    }

    .swipe-zone {
        touch-action: pan-y;
        overflow: hidden;
    }
</style>

<div class="swipe-zone" id="swipeZone">
    <div class="menu-tabs" id="menuTabs">
        <div class="menu-tab active" data-tab="featured">Featured</div>
        <div class="menu-tab" data-tab="combo">Combo</div>
        <div class="menu-tab" data-tab="momo">Momo</div>
        <div class="menu-tab" data-tab="drinks">Drinks</div>
        <div class="menu-tab" data-tab="all">All</div>
    </div>

    <div class="menu-content" id="tabContent">
        <h3>🔥 Featured Items</h3>
        <p>Start designing your featured products here.</p>
    </div>
</div>

<script>
    const tabs = ['featured', 'combo', 'momo', 'drinks', 'all'];
    let currentIndex = 0;

    const tabButtons = document.querySelectorAll('.menu-tab');
    const tabContent = document.getElementById('tabContent');

    const contentTemplates = {
        featured: `<h3>🔥 Featured Items</h3><p>Start designing your featured products here.</p>`,
        combo: `<h3>🥡 Combo Sets</h3><p>List your combos here.</p>`,
        momo: `<h3>🥟 Momos</h3><p>List momo types here.</p>`,
        drinks: `<h3>🥤 Drinks</h3><p>List your drinks here.</p>`,
        all: `<h3>🍽 All Menu Items</h3><p>All items here.</p>`,
    };

    function activateTab(tabName) {
        tabButtons.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabName);
        });
        tabContent.innerHTML = contentTemplates[tabName] || `<p>No content</p>`;
        currentIndex = tabs.indexOf(tabName);
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => activateTab(btn.dataset.tab));
    });

    // Swipe Logic
    let startX = 0;
    const swipeZone = document.getElementById('swipeZone');

    swipeZone.addEventListener('touchstart', e => {
        startX = e.touches[0].clientX;
    });

    swipeZone.addEventListener('touchend', e => {
        const endX = e.changedTouches[0].clientX;
        const deltaX = endX - startX;

        if (Math.abs(deltaX) > 50) {
            if (deltaX < 0 && currentIndex < tabs.length - 1) {
                activateTab(tabs[currentIndex + 1]);
            } else if (deltaX > 0 && currentIndex > 0) {
                activateTab(tabs[currentIndex - 1]);
            }
        }
    });
</script>
@endsection
