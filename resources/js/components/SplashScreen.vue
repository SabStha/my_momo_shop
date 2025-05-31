<template>
    <div class="splash-screen">
      <img src="/storage/logo/momo_icon.png" alt="Logo" class="logo" />
      <h1 class="title">AmaKo MOMO</h1>
    </div>
  </template>
  
  <style scoped>
  .splash-screen {
    height: 100vh;
    background: url('/storage/products/splash-bg.png') center/cover no-repeat;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }
  .logo {
    height: 80px;
    animation: fadeIn 2s ease;
  }
  .title {
    color: white;
    margin-top: 1rem;
    font-size: 2rem;
    animation: fadeIn 2.5s ease;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  </style>
  