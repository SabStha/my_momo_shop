// resources/js/app.js
import './bootstrap';
import 'bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import PaymentManager from './components/PaymentManager.vue';
import PosApp from './components/PosApp.vue';

// Define Vue feature flags
window.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false;

// Create and mount the app only if the container exists and no app is mounted
const appElement = document.getElementById('app');
if (appElement && !appElement._vue) {
    const app = createApp(App);
    app.mount('#app');
}

// Mount PaymentManager if container exists
const paymentManagerElement = document.getElementById('payment-manager');
if (paymentManagerElement && !paymentManagerElement._vue) {
    const paymentManager = createApp(PaymentManager);
    paymentManager.mount('#payment-manager');
}

// Mount PosApp if container exists
const posAppElement = document.getElementById('pos-app');
if (posAppElement && !posAppElement._vue) {
    const posApp = createApp(PosApp);
    posApp.mount('#pos-app');
}
