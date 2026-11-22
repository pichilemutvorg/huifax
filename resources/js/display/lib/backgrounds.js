/**
 * Background to show: the slide's own background wins; otherwise the general
 * rotation, or none when there are no backgrounds.
 *
 * @param {{ background?: string | null } | null} slide
 * @param {Array<{ url: string }>} rotation
 * @param {number} index Position in the general rotation (wraps around).
 * @returns {string | null}
 */
export function pickBackground(slide, rotation, index) {
    if (slide?.background) {
        return slide.background;
    }

    if (rotation.length === 0) {
        return null;
    }

    return rotation[((index % rotation.length) + rotation.length) % rotation.length].url;
}
