// resources/js/app.js
import './bootstrap';
import '../css/app.css';
import { createApp } from 'vue';
import App from './App.vue';
import PaymentManager from './components/PaymentManager.vue';
import PosApp from './components/PosApp.vue';

// Import your Vue components here
// import ExampleComponent from './components/ExampleComponent.vue';

// Import your CSS
import '../css/app.css';

// Vue hydration flag (you can remove this if you're not doing SSR/hydration)
window.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false;

const app = createApp({});

// Register your components here
// app.component('example-component', ExampleComponent);

// Mount Vue app if #app element exists and hasn't been mounted yet
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
