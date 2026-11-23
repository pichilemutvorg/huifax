/**
 * Plays a live audio stream and keeps it playing: reconnects on errors, on
 * the stream ending and when playback silently freezes, waiting longer after
 * each consecutive failure (exponential backoff).
 *
 * @param {object} [options]
 * @param {() => HTMLAudioElement} [options.createAudio]
 * @param {number} [options.minDelay] First retry delay, in seconds.
 * @param {number} [options.maxDelay] Upper bound for the retry delay, in seconds.
 * @param {number} [options.checkEvery] Seconds between playback progress checks.
 * @param {number} [options.maxStrikes] Checks without progress before reconnecting.
 * @param {(message: string, detail?: unknown) => void} [options.log]
 */
export function createRadioPlayer({
    createAudio = () => new Audio(),
    minDelay = 2,
    maxDelay = 60,
    checkEvery = 10,
    maxStrikes = 2,
    log = (message, detail) => console.warn(`[radio] ${message}`, detail ?? ''),
} = {}) {
    const audio = createAudio();
    audio.preload = 'none';

    let url = null;
    let failures = 0;
    let retryTimer = null;
    let watchdog = null;
    let lastTime = -1;
    let strikes = 0;

    const listeners = {
        playing: () => {
            failures = 0;
            strikes = 0;
        },
        error: () => reconnect('stream error', audio.error),
        ended: () => reconnect('stream ended'),
    };

    for (const [event, handler] of Object.entries(listeners)) {
        audio.addEventListener(event, handler);
    }

    function start() {
        clearTimeout(retryTimer);
        retryTimer = null;
        lastTime = -1;
        strikes = 0;

        audio.src = url;
        // Rejected play() covers autoplay policies and unreachable streams.
        audio.play()?.catch?.((error) => reconnect('play() rejected', error));

        clearInterval(watchdog);
        watchdog = setInterval(checkProgress, checkEvery * 1000);
    }

    function checkProgress() {
        if (audio.currentTime !== lastTime) {
            lastTime = audio.currentTime;
            strikes = 0;
            return;
        }

        strikes++;
        if (strikes >= maxStrikes) {
            reconnect('playback froze');
        }
    }

    /** Drop the connection and its buffers, then retry after a growing delay. */
    function reconnect(reason, detail) {
        if (url === null || retryTimer !== null) {
            return;
        }

        release();
        const delay = retryDelay(failures, minDelay, maxDelay);
        failures++;
        log(`${reason}; retrying in ${delay} s`, detail);
        retryTimer = setTimeout(start, delay * 1000);
    }

    function release() {
        clearInterval(watchdog);
        watchdog = null;
        audio.pause();
        audio.removeAttribute('src');
        audio.load();
    }

    return {
        /**
         * Apply the audio settings. Safe to call repeatedly: playback only
         * restarts when the stream URL changes.
         *
         * @param {{ enabled?: boolean, stream_url?: string | null, volume?: number }} settings
         */
        configure({ enabled, stream_url: streamUrl, volume } = {}) {
            audio.volume = Math.min(1, Math.max(0, (Number(volume) || 0) / 100));

            const next = enabled && streamUrl ? streamUrl : null;
            if (next === url) {
                return;
            }

            this.stop();
            url = next;
            failures = 0;
            if (url !== null) {
                start();
            }
        },

        stop() {
            clearTimeout(retryTimer);
            retryTimer = null;
            if (url !== null) {
                release();
            }
            url = null;
        },

        destroy() {
            this.stop();
            for (const [event, handler] of Object.entries(listeners)) {
                audio.removeEventListener(event, handler);
            }
        },
    };
}

/**
 * Seconds to wait before the next attempt after `failures` consecutive failures.
 *
 * @param {number} failures
 * @param {number} min
 * @param {number} max
 */
export function retryDelay(failures, min, max) {
    return Math.min(max, min * 2 ** failures);
}
