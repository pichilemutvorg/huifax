import { onBeforeUnmount, onMounted, shallowRef } from 'vue';
import { pickNextSlide, pickPreviousSlide } from '../lib/rotation.js';

/** Seconds the idle screen stays before checking again for content. */
const IDLE_SECONDS = 10;

/** Seconds to wait after a slide fails to render before moving on. */
const SKIP_DELAY_SECONDS = 1;

/**
 * Drives the slide rotation. Pending content is applied only between slides,
 * and slides that fail to render are skipped until the content changes.
 *
 * @param {import('vue').ShallowRef<{ slides: Array<{ id: string, duration: number }> }>} content
 * @param {() => boolean} applyPending
 */
export function useRotation(content, applyPending) {
    const current = shallowRef(null);
    const failed = new Set();
    let timer = null;

    const playable = () => content.value.slides.filter((slide) => !failed.has(slide.id));

    function show(slide) {
        current.value = slide;
        clearTimeout(timer);
        timer = setTimeout(advance, (slide?.duration ?? IDLE_SECONDS) * 1000);
        preloadImage(pickNextSlide(playable(), playable(), slide?.id ?? null));
    }

    /** Fetch the next slide's image ahead of time so it appears complete. */
    function preloadImage(slide) {
        if (slide?.data?.image) {
            new Image().src = slide.data.image;
        }
    }

    function advance() {
        const previous = playable();
        if (applyPending()) {
            failed.clear();
        }
        show(pickNextSlide(previous, playable(), current.value?.id ?? null));
    }

    /** Called when the slide on screen throws while rendering. */
    function skip(slideId) {
        if (slideId) {
            failed.add(slideId);
        }
        clearTimeout(timer);
        timer = setTimeout(advance, SKIP_DELAY_SECONDS * 1000);
    }

    /** Manual navigation: the chosen slide gets its full duration. */
    function next() {
        advance();
    }

    function previous() {
        if (applyPending()) {
            failed.clear();
        }
        show(pickPreviousSlide(playable(), current.value?.id ?? null));
    }

    onMounted(() => show(pickNextSlide([], playable(), null)));
    onBeforeUnmount(() => clearTimeout(timer));

    return { current, skip, next, previous };
}
