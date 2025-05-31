<template>
    <div id="app">
      <transition name="fade" mode="out-in">
        <SplashScreen v-if="loading" />
        <MainApp v-else />
      </transition>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import SplashScreen from './components/SplashScreen.vue'
  import MainApp from './components/MainApp.vue'
  
  const loading = ref(true)
  
  onMounted(() => {
    setTimeout(() => {
      loading.value = false
    }, 2500)
  })
  </script>
  
  <style>
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
  }
  </style>
  