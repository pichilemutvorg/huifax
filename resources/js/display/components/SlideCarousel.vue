<script setup>
import { onErrorCaptured } from 'vue';
import { slideComponents } from '../slides/index.js';
import IdleSlide from '../slides/IdleSlide.vue';

const props = defineProps({
    slide: { type: Object, default: null },
    settings: { type: Object, required: true },
    strings: { type: Object, required: true },
});

const emit = defineEmits(['error']);

// A slide that throws must never stop the rotation: report it and move on.
onErrorCaptured((error, instance, info) => {
    console.error('[display] Slide failed to render', props.slide?.id, info, error);
    emit('error', props.slide?.id ?? null);

    return false;
});
</script>

<template>
    <!--
        Each slide gets a fresh wrapper (new key), which replays the CSS entry
        animation. No Vue <Transition>: it waits on animation frames and
        transitionend events, and if those stall the slide stays hidden.
    -->
    <div :key="slide?.id ?? 'idle'" class="slide-enter h-full">
        <component
            :is="slideComponents[slide.type]"
            v-if="slide"
            :data="slide.data"
            :strings="strings"
            :settings="settings"
        />
        <IdleSlide v-else :settings="settings" />
    </div>
</template>
