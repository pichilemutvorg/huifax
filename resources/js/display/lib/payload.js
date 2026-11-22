/**
 * Validates a payload from the server and drops anything the display cannot
 * render. Returns null when the payload as a whole is unusable, so the caller
 * keeps showing the content it already has.
 *
 * @param {unknown} payload
 * @param {Set<string>} knownTypes Slide types with a registered component.
 * @returns {{ settings: object, strings: object, slides: Array<{ id: string, type: string, duration: number, data: object }> } | null}
 */
export function normalizePayload(payload, knownTypes) {
    if (!isObject(payload) || !isObject(payload.settings) || !Array.isArray(payload.slides)) {
        return null;
    }

    return {
        settings: payload.settings,
        strings: isObject(payload.strings) ? payload.strings : {},
        slides: payload.slides.filter((slide) => isValidSlide(slide) && knownTypes.has(slide.type)),
    };
}

function isValidSlide(slide) {
    return (
        isObject(slide) &&
        typeof slide.id === 'string' &&
        typeof slide.type === 'string' &&
        Number.isFinite(slide.duration) &&
        slide.duration > 0 &&
        isObject(slide.data)
    );
}

function isObject(value) {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}
