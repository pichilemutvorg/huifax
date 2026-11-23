<script setup>
import { computed, ref, watchEffect } from 'vue';
import BackgroundLayer from './components/BackgroundLayer.vue';
import DisplayFooter from './components/DisplayFooter.vue';
import DisplayHeader from './components/DisplayHeader.vue';
import DisplayStage from './components/DisplayStage.vue';
import SafeAreaGuides from './components/SafeAreaGuides.vue';
import SlideCarousel from './components/SlideCarousel.vue';
import { useBackground } from './composables/useBackground.js';
import { useDisplayContent } from './composables/useDisplayContent.js';
import { useKeyboardControls } from './composables/useKeyboardControls.js';
import { useRadio } from './composables/useRadio.js';
import { useRotation } from './composables/useRotation.js';
import { useScheduledReload } from './composables/useScheduledReload.js';
import { slideComponents } from './slides/index.js';

const props = defineProps({
    initial: { type: Object, default: null },
    etag: { type: String, default: null },
    apiUrl: { type: String, required: true },
    healthUrl: { type: String, required: true },
    // Start with the safe area guides visible (`?guides` in the URL).
    guides: { type: Boolean, default: false },
    // Theme forced from the URL (`?theme=`), validated by the server.
    themeOverride: { type: String, default: null },
});

const { content, applyPending } = useDisplayContent({
    initial: props.initial,
    etag: props.etag,
    url: props.apiUrl,
    knownTypes: new Set(Object.keys(slideComponents)),
});

const settings = computed(() => content.value.settings);

// Themes are pure CSS keyed by data-theme. Like any content change, a new
// theme arrives between slides.
watchEffect(() => {
    document.documentElement.dataset.theme = props.themeOverride || settings.value.theme || 'huifax';
});
const strings = computed(() => content.value.strings);

const { current, skip, next, previous } = useRotation(content, applyPending);
const showGuides = ref(props.guides);

useKeyboardControls({ next, previous, toggleGuides: () => (showGuides.value = !showGuides.value) });
const { url: backgroundUrl } = useBackground(content, current);

useRadio(() => settings.value.audio);

useScheduledReload(
    () => settings.value.reload_at,
    () => settings.value.timezone,
    props.healthUrl,
);
</script>

<template>
    <DisplayStage>
        <BackgroundLayer :url="backgroundUrl" />
        <!-- Content margins: 5% on every side, the title safe area. -->
        <div class="relative flex h-full flex-col px-24 py-14">
            <DisplayHeader :settings="settings" />
            <main class="min-h-0 flex-1 py-12">
                <SlideCarousel :slide="current" :settings="settings" :strings="strings" @error="skip" />
            </main>
            <DisplayFooter :settings="settings" />
        </div>
        <SafeAreaGuides v-if="showGuides" />
    </DisplayStage>
</template>
