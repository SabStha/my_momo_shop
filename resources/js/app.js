// resources/js/app.js
import './bootstrap';
import 'bootstrap';
import { createApp } from 'vue';
import App from './App.vue';

// Define Vue feature flags
window.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false;

// Create and mount the app only if the container exists and no app is mounted
const appElement = document.getElementById('app');
if (appElement && !appElement._vue) {
    const app = createApp(App);
    app.mount('#app');
}
