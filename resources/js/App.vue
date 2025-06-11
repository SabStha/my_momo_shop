<template>
  <TopNav />
  <main class="pt-14 pb-14 min-h-screen bg-gray-50">
    <router-view />
  </main>
  <BottomNav />
</template>

<script setup>
import TopNav from './components/layout/TopNav.vue'
import BottomNav from './components/layout/BottomNav.vue'
</script>
