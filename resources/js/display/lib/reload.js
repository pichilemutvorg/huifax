/** Minimum uptime before a scheduled reload, so a reload can't trigger another. */
export const MIN_UPTIME_MINUTES = 60;

/** Minutes after `reloadAt` during which a reload may still happen (e.g. after a failed health check). */
export const RELOAD_WINDOW_MINUTES = 30;

/**
 * Whether the display should do its daily safety reload now.
 *
 * @param {string} localTime Current time as HH:MM in the instance timezone.
 * @param {string | null} reloadAt Configured HH:MM, or null when disabled.
 * @param {number} uptimeMinutes Minutes since the page loaded.
 */
export function shouldReload(localTime, reloadAt, uptimeMinutes) {
    if (!reloadAt || uptimeMinutes < MIN_UPTIME_MINUTES) {
        return false;
    }

    const now = toMinutes(localTime);
    const target = toMinutes(reloadAt);
    if (now === null || target === null) {
        return false;
    }

    // Minutes elapsed since the target time, wrapping around midnight.
    const elapsed = (now - target + 24 * 60) % (24 * 60);

    return elapsed < RELOAD_WINDOW_MINUTES;
}

function toMinutes(value) {
    const match = /^(\d{1,2}):(\d{2})$/.exec(String(value));
    if (!match) {
        return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);
    if (hours > 23 || minutes > 59) {
        return null;
    }

    return hours * 60 + minutes;
}

/** Query parameter that makes each scheduled reload a navigation to a new URL. */
export const RELOAD_PARAM = 'reload';

/**
 * URL for the scheduled reload: the same page with a fresh `reload` parameter.
 * CasparCG's HTML producer (CEF) hangs on same-URL navigations such as
 * `location.reload()`, while navigating to a different URL works. Other query
 * parameters (such as guides) are kept.
 *
 * @param {string} href Current page URL.
 * @param {number} now Timestamp that makes the URL unique.
 */
export function reloadUrl(href, now) {
    const url = new URL(href);
    url.searchParams.set(RELOAD_PARAM, String(now));
    url.hash = '';

    return url.toString();
}
