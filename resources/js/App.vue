<template>
    <div id="app">
      <PosApp />
    </div>
</template>

<script setup>
import PosApp from './components/PosApp.vue'
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
  