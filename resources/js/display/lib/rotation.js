/**
 * Picks the slide to show after `currentId`.
 *
 * The rotation may have changed in between (content updates are applied at
 * transitions), so the current slide is looked up in the previous list and
 * the rotation continues from the same position in the new one:
 * - if the current slide still exists, show the one after it;
 * - if it was removed, show whatever now occupies the next position;
 * - with no current slide, start from the beginning.
 *
 * @template {{ id: string }} T
 * @param {T[]} previous Rotation the current slide belongs to.
 * @param {T[]} next Rotation to continue with (may be the same array).
 * @param {string | null} currentId
 * @returns {T | null} Null when there is nothing to show.
 */
export function pickNextSlide(previous, next, currentId) {
    if (next.length === 0) {
        return null;
    }

    if (currentId === null) {
        return next[0];
    }

    const indexInNext = next.findIndex((slide) => slide.id === currentId);
    if (indexInNext !== -1) {
        return next[(indexInNext + 1) % next.length];
    }

    const indexInPrevious = previous.findIndex((slide) => slide.id === currentId);
    if (indexInPrevious === -1) {
        return next[0];
    }

    // The current slide is gone: the slides after it shifted one place back,
    // so its old index now points at the slide that would have come next.
    return next[indexInPrevious % next.length];
}
