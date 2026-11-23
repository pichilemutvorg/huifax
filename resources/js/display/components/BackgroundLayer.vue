<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    url: { type: String, default: null },
});

/** Layers on screen: the newest fades in over the previous one. At most two. */
const layers = ref([]);
let nextKey = 0;
let loader = null;

function show(url) {
    if (layers.value.at(-1)?.url === url) {
        return;
    }

    if (!url) {
        layers.value = [];
        return;
    }

    // Preload first: a broken or slow image never replaces the one on screen.
    loader?.removeAttribute('src');
    loader = new Image();
    loader.onload = () => {
        layers.value = [...layers.value.slice(-1), { key: nextKey++, url }];
    };
    loader.src = url;
}

watch(() => props.url, show, { immediate: true });

onBeforeUnmount(() => {
    loader?.removeAttribute('src');
    loader = null;
});
</script>

<template>
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <img
            v-for="layer in layers"
            :key="layer.key"
            :src="layer.url"
            alt=""
            class="background-enter absolute inset-0 h-full w-full object-cover"
        />
        <!-- Keeps text contrast on any photo; how much is up to the theme. -->
        <div v-if="layers.length" class="hx-scrim absolute inset-0" />
    </div>
</template>
