// resources/js/app.js

import { createApp } from 'vue';

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
    app.mount('#app');
}
