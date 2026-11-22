/**
 * Polls the display API using ETags. Unchanged content answers 304 and costs
 * almost nothing; failures back off exponentially up to `maxBackoff`.
 *
 * Errors never escape: the display keeps its current content until a valid
 * response arrives.
 *
 * @param {object} options
 * @param {string} options.url
 * @param {string | null} options.etag ETag of the content already on screen.
 * @param {() => number} options.interval Seconds between polls; read on every cycle.
 * @param {(payload: unknown, etag: string | null) => void} options.onContent
 * @param {number} [options.maxBackoff] Upper bound for the retry delay, in seconds.
 * @param {number} [options.timeout] Request timeout, in seconds.
 * @param {typeof fetch} [options.fetch]
 */
export function createPoller({
    url,
    etag,
    interval,
    onContent,
    maxBackoff = 300,
    timeout = 15,
    fetch = globalThis.fetch,
}) {
    let currentEtag = etag;
    let failures = 0;
    let timer = null;
    let stopped = true;

    async function poll() {
        try {
            const headers = { Accept: 'application/json' };
            if (currentEtag) {
                headers['If-None-Match'] = currentEtag;
            }

            const response = await fetch(url, {
                headers,
                cache: 'no-store',
                signal: AbortSignal.timeout(timeout * 1000),
            });

            if (response.status === 304) {
                failures = 0;
            } else if (response.ok) {
                const payload = await response.json();
                currentEtag = response.headers.get('ETag');
                failures = 0;
                onContent(payload, currentEtag);
            } else {
                failures++;
            }
        } catch {
            failures++;
        }

        schedule();
    }

    function schedule() {
        if (stopped) {
            return;
        }

        timer = setTimeout(poll, nextDelay(interval(), failures, maxBackoff) * 1000);
    }

    return {
        start() {
            if (!stopped) {
                return;
            }
            stopped = false;
            schedule();
        },
        stop() {
            stopped = true;
            clearTimeout(timer);
            timer = null;
        },
        /** Forget the known ETag so the next poll fetches full content. */
        invalidate() {
            currentEtag = null;
        },
    };
}

/**
 * Seconds to wait before the next poll.
 *
 * @param {number} interval Regular interval, in seconds.
 * @param {number} failures Consecutive failed polls.
 * @param {number} maxBackoff
 */
export function nextDelay(interval, failures, maxBackoff) {
    const base = Number.isFinite(interval) && interval > 0 ? interval : 30;
    if (failures === 0) {
        return base;
    }

    return Math.min(base * 2 ** failures, Math.max(maxBackoff, base));
}
