/**
 * When each slide of a rotation goes on air, counting from the start of the
 * cycle, and how long a full cycle lasts.
 *
 * @template {{ duration: number }} T
 * @param {T[]} slides
 * @returns {{ items: Array<{ slide: T, start: number, end: number }>, total: number }}
 */
export function timeline(slides) {
    let elapsed = 0;
    const items = slides.map((slide) => {
        const start = elapsed;
        elapsed += slide.duration;
        return { slide, start, end: elapsed };
    });

    return { items, total: elapsed };
}

/**
 * Seconds as m:ss (or h:mm:ss for an hour or more).
 *
 * @param {number} seconds
 */
export function formatClock(seconds) {
    const s = Math.max(0, Math.round(seconds));
    const pad = (n) => String(n).padStart(2, '0');
    const hours = Math.floor(s / 3600);
    const minutes = Math.floor((s % 3600) / 60);

    return hours > 0 ? `${hours}:${pad(minutes)}:${pad(s % 60)}` : `${minutes}:${pad(s % 60)}`;
}
