// resources/js/app.js
import './bootstrap';
import 'bootstrap';
import { createApp } from 'vue';
import PosApp from './components/PosApp.vue';
import PaymentManager from './components/PaymentManager.vue';
import ReportManager from './components/ReportManager.vue';

// Only mount PosApp if #pos-app exists
if (document.getElementById('pos-app')) {
    createApp(PosApp).mount('#pos-app');
}

// Only mount PaymentManager if #payment-manager exists
if (document.getElementById('payment-manager')) {
    createApp(PaymentManager).mount('#payment-manager');
}

// Only mount ReportManager if #admin-report-manager-app exists
if (document.getElementById('admin-report-manager-app')) {
    const app = createApp({});
    app.component('report-manager', ReportManager);
    app.mount('#admin-report-manager-app');
}
// Only mount ReportManager if #dashboard-report-manager-app exists
if (document.getElementById('dashboard-report-manager-app')) {
    const app = createApp({});
    app.component('report-manager', ReportManager);
    app.mount('#dashboard-report-manager-app');
}
