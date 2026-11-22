import { createApp } from 'vue';
import App from './App.vue';

const root = document.getElementById('display');

function readInitialPayload() {
    try {
        return JSON.parse(document.getElementById('display-payload')?.textContent ?? 'null');
    } catch (error) {
        console.error('[display] Invalid embedded payload', error);
        return null;
    }
}

const app = createApp(App, {
    initial: readInitialPayload(),
    etag: root.dataset.etag || null,
    apiUrl: root.dataset.apiUrl,
    healthUrl: root.dataset.healthUrl,
});

// Log and carry on: an error must never leave the screen blank.
app.config.errorHandler = (error, instance, info) => {
    console.error('[display] Unhandled error', info, error);
};

app.mount(root);
