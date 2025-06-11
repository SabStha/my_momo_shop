<template>
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t shadow-inner">
      <div class="flex justify-around items-center h-14">
        <RouterLink to="/" class="flex flex-col items-center text-sm">
          <Home class="w-5 h-5" /> Home
        </RouterLink>
        <RouterLink to="/menu" class="flex flex-col items-center text-sm">
          <Utensils class="w-5 h-5" /> Menu
        </RouterLink>
        <RouterLink to="/bulk" class="flex flex-col items-center text-sm">
          <Package class="w-5 h-5" /> Bulk
        </RouterLink>
        <RouterLink to="/amas" class="flex flex-col items-center text-sm">
          <Gift class="w-5 h-5" /> Ama’s
        </RouterLink>
        <RouterLink to="/profile" class="flex flex-col items-center text-sm">
          <User class="w-5 h-5" /> Profile
        </RouterLink>
      </div>
    </nav>
  </template>
  
  <script setup>
  import { Home, Package, Gift, User, Utensils } from 'lucide-vue-next'
  import { RouterLink } from 'vue-router'
  </script>
  