<script setup>
import { computed } from 'vue';
import BackgroundLayer from './components/BackgroundLayer.vue';
import DisplayFooter from './components/DisplayFooter.vue';
import DisplayHeader from './components/DisplayHeader.vue';
import DisplayStage from './components/DisplayStage.vue';
import SlideCarousel from './components/SlideCarousel.vue';
import { useBackground } from './composables/useBackground.js';
import { useDisplayContent } from './composables/useDisplayContent.js';
import { useRotation } from './composables/useRotation.js';
import { useScheduledReload } from './composables/useScheduledReload.js';
import { slideComponents } from './slides/index.js';

const props = defineProps({
    initial: { type: Object, default: null },
    etag: { type: String, default: null },
    apiUrl: { type: String, required: true },
    healthUrl: { type: String, required: true },
});

const { content, applyPending } = useDisplayContent({
    initial: props.initial,
    etag: props.etag,
    url: props.apiUrl,
    knownTypes: new Set(Object.keys(slideComponents)),
});

const settings = computed(() => content.value.settings);
const strings = computed(() => content.value.strings);

const { current, skip } = useRotation(content, applyPending);
const { url: backgroundUrl } = useBackground(content, current);

useScheduledReload(
    () => settings.value.reload_at,
    () => settings.value.timezone,
    props.healthUrl,
);
</script>

<template>
    <DisplayStage>
        <BackgroundLayer :url="backgroundUrl" />
        <!-- Safe area: ~5% horizontal and vertical margins for TV overscan. -->
        <div class="relative flex h-full flex-col px-24 py-14">
            <DisplayHeader :settings="settings" />
            <main class="min-h-0 flex-1 py-12">
                <SlideCarousel :slide="current" :settings="settings" :strings="strings" @error="skip" />
            </main>
            <DisplayFooter :settings="settings" />
        </div>
    </DisplayStage>
</template>
