import { createApp } from 'vue';
import App from './App.vue';

const root = document.getElementById('display');

createApp(App, {
    locale: root.dataset.locale,
    timezone: root.dataset.timezone,
}).mount(root);
