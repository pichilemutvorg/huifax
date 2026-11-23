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
    guides: new URLSearchParams(window.location.search).has('guides'),
    themeOverride: root.dataset.themeOverride || null,
});

// Log and carry on: an error must never leave the screen blank.
app.config.errorHandler = (error, instance, info) => {
    console.error('[display] Unhandled error', info, error);
};

app.mount(root);

// Lets the display start from its last good copy when the server is down.
if ('serviceWorker' in navigator && root.dataset.serviceWorkerUrl) {
    navigator.serviceWorker.register(root.dataset.serviceWorkerUrl).catch((error) => {
        console.warn('[display] Service worker not registered', error);
    });
}
