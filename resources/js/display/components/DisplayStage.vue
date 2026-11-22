<script setup>
import { STAGE_HEIGHT, STAGE_WIDTH, useStageScale } from '../composables/useStageScale.js';

const { scale } = useStageScale();
</script>

<template>
    <div class="flex h-full w-full items-center justify-center overflow-hidden">
        <div
            class="relative shrink-0 overflow-hidden bg-base-100 text-base-content"
            :style="{ width: `${STAGE_WIDTH}px`, height: `${STAGE_HEIGHT}px`, transform: `scale(${scale})` }"
        >
            <slot />
        </div>
    </div>
</template>
