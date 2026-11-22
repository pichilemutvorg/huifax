import { onBeforeUnmount, onMounted, shallowRef } from 'vue';
import { createPoller } from '../lib/poller.js';
import { normalizePayload } from '../lib/payload.js';

/**
 * Display content with deferred updates.
 *
 * `content` is what is on screen. New content from the server is kept in
 * `pending` and only becomes `content` when `applyPending()` is called, which
 * the carousel does between slides, so a slide on air never changes.
 *
 * @param {object} options
 * @param {object} options.initial Payload embedded in the page.
 * @param {string | null} options.etag
 * @param {string} options.url
 * @param {Set<string>} options.knownTypes
 */
export function useDisplayContent({ initial, etag, url, knownTypes }) {
    const empty = { settings: {}, strings: {}, backgrounds: [], slides: [] };
    const content = shallowRef(normalizePayload(initial, knownTypes) ?? empty);
    let pending = null;

    const poller = createPoller({
        url,
        etag,
        interval: () => content.value.settings.poll_interval,
        onContent(payload) {
            const normalized = normalizePayload(payload, knownTypes);
            if (normalized) {
                pending = normalized;
            }
        },
    });

    onMounted(() => poller.start());
    onBeforeUnmount(() => poller.stop());

    return {
        content,
        /** Swap in the latest content, if any arrived. Returns true when it changed. */
        applyPending() {
            if (!pending) {
                return false;
            }
            content.value = pending;
            pending = null;
            return true;
        },
    };
}
