<template>
    <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b shadow-sm">
      <div class="flex items-center justify-between px-4 h-14">
        <h1 class="text-lg font-bold text-[#6E0D25]">AmaKo MOMO</h1>
        <div class="flex gap-4">
          <button>
            <Bell class="w-5 h-5 text-gray-600" />
          </button>
          <button>
            <ShoppingCart class="w-5 h-5 text-gray-600" />
          </button>
        </div>
      </div>
    </header>
  </template>
  
  <script setup>
  import { Bell, ShoppingCart } from 'lucide-vue-next'
  </script>
  