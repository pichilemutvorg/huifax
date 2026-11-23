<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { STAGE_HEIGHT, STAGE_WIDTH } from '../display/composables/useStageScale.js';

/** A full 1920×1080 stage scaled down to the width of its container. */
const box = ref(null);
const scale = ref(0.2);
let observer = null;

onMounted(() => {
    observer = new ResizeObserver(([entry]) => {
        scale.value = entry.contentRect.width / STAGE_WIDTH;
    });
    observer.observe(box.value);
});
onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <div ref="box" class="relative aspect-video w-full overflow-hidden">
        <div
            class="absolute top-0 left-0 origin-top-left overflow-hidden bg-base-100 text-base-content"
            :style="{ width: `${STAGE_WIDTH}px`, height: `${STAGE_HEIGHT}px`, transform: `scale(${scale})` }"
        >
            <slot />
        </div>
    </div>
</template>
