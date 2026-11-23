import { onBeforeUnmount, onMounted, toValue } from 'vue';
import { reloadUrl, shouldReload } from '../lib/reload.js';

const CHECK_EVERY_MS = 60_000;

/**
 * Daily safety reload. Before reloading it checks that the server answers,
 * so a reload never replaces a working display with a browser error page.
 *
 * @param {import('vue').MaybeRefOrGetter<string | null>} reloadAt HH:MM in the instance timezone.
 * @param {import('vue').MaybeRefOrGetter<string>} timeZone
 * @param {string} healthUrl
 */
export function useScheduledReload(reloadAt, timeZone, healthUrl) {
    const startedAt = Date.now();
    let timer = null;
    let checking = false;

    async function check() {
        if (checking) {
            return;
        }

        const localTime = new Intl.DateTimeFormat('en-GB', {
            timeZone: toValue(timeZone) || undefined,
            hour: '2-digit',
            minute: '2-digit',
            hourCycle: 'h23',
        }).format(new Date());
        const uptimeMinutes = (Date.now() - startedAt) / 60_000;

        if (!shouldReload(localTime, toValue(reloadAt), uptimeMinutes)) {
            return;
        }

        checking = true;
        try {
            const response = await fetch(healthUrl, { cache: 'no-store', signal: AbortSignal.timeout(10_000) });
            if (response.ok) {
                // Not location.reload(): see reloadUrl().
                window.location.assign(reloadUrl(window.location.href, Date.now()));
            }
        } catch {
            // Server unreachable: keep running and try again on the next check.
        } finally {
            checking = false;
        }
    }

    onMounted(() => {
        timer = setInterval(check, CHECK_EVERY_MS);
    });
    onBeforeUnmount(() => clearInterval(timer));
}
