/**
 * Validates a payload from the server and drops anything the display cannot
 * render. Returns null when the payload as a whole is unusable, so the caller
 * keeps showing the content it already has.
 *
 * @param {unknown} payload
 * @param {Set<string>} knownTypes Slide types with a registered component.
 * @returns {{ settings: object, strings: object, backgrounds: Array<{ id: number, url: string }>, slides: Array<{ id: string, type: string, duration: number, background: string | null, data: object }> } | null}
 */
export function normalizePayload(payload, knownTypes) {
    if (!isObject(payload) || !isObject(payload.settings) || !Array.isArray(payload.slides)) {
        return null;
    }

    return {
        settings: payload.settings,
        strings: isObject(payload.strings) ? payload.strings : {},
        backgrounds: Array.isArray(payload.backgrounds) ? payload.backgrounds.filter(isValidBackground) : [],
        slides: payload.slides
            .filter((slide) => isValidSlide(slide) && knownTypes.has(slide.type))
            .map((slide) => ({ ...slide, background: isNonEmptyString(slide.background) ? slide.background : null })),
    };
}

function isValidBackground(background) {
    return isObject(background) && isNonEmptyString(background.url);
}

function isNonEmptyString(value) {
    return typeof value === 'string' && value !== '';
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
