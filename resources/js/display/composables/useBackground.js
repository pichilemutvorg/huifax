import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { pickBackground } from '../lib/backgrounds.js';

const DEFAULT_INTERVAL_SECONDS = 60;

/**
 * Current background URL. The general rotation advances on its own interval,
 * independent of slides; a slide with its own background overrides it while
 * on screen.
 *
 * @param {import('vue').ShallowRef<{ settings: object, backgrounds: Array<{ url: string }> }>} content
 * @param {import('vue').ShallowRef<{ background?: string | null } | null>} currentSlide
 */
export function useBackground(content, currentSlide) {
    const index = ref(0);
    let timer = null;

    function schedule() {
        const seconds = Number(content.value.settings.background_interval);
        const interval = Number.isFinite(seconds) && seconds > 0 ? seconds : DEFAULT_INTERVAL_SECONDS;

        timer = setTimeout(() => {
            index.value++;
            schedule();
        }, interval * 1000);
    }

    onMounted(schedule);
    onBeforeUnmount(() => clearTimeout(timer));

    return {
        url: computed(() => pickBackground(currentSlide.value, content.value.backgrounds, index.value)),
    };
}
