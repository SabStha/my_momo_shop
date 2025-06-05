// resources/js/app.js

import { createApp } from 'vue';
import App from './App.vue';

// Optional: Import your CSS if you're not using @vite in Blade
import '../css/app.css';

// Vue hydration flag (you can remove this if you're not doing SSR/hydration)
window.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false;

// Mount Vue app if #app element exists and hasn't been mounted yet
const appElement = document.getElementById('app');
if (appElement && !appElement._vue) {
    const app = createApp(App);
    app.mount('#app');
}
