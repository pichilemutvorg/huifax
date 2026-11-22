import { onBeforeUnmount, onMounted, ref } from 'vue';

export const STAGE_WIDTH = 1920;
export const STAGE_HEIGHT = 1080;

/**
 * Scale factor that fits the fixed 1920×1080 stage inside the window,
 * keeping its 16:9 aspect ratio.
 */
export function useStageScale() {
    const scale = ref(1);

    function update() {
        scale.value = Math.min(window.innerWidth / STAGE_WIDTH, window.innerHeight / STAGE_HEIGHT);
    }

    onMounted(() => {
        update();
        window.addEventListener('resize', update);
    });
    onBeforeUnmount(() => window.removeEventListener('resize', update));

    return { scale };
}
