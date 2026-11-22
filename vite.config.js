import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/display.css', 'resources/js/display/main.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    build: {
        // CasparCG 2.4+ ships a modern CEF (Chromium). Pin this to its exact version once known.
        target: 'chrome110',
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
