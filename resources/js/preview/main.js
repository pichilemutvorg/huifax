import { createApp } from 'vue';
import PreviewApp from './PreviewApp.vue';

const data = JSON.parse(document.getElementById('preview-data').textContent);

createApp(PreviewApp, data).mount('#preview');
