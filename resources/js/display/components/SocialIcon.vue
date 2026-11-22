<script setup>
import { computed } from 'vue';
import { socialIcons } from '../icons/socialIcons.js';

const props = defineProps({
    platform: { type: String, required: true },
});

const icon = computed(() => socialIcons[props.platform] ?? null);
</script>

<template>
    <svg v-if="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path :d="icon.path" />
    </svg>
    <svg
        v-else
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.25"
        stroke-linecap="round"
        aria-hidden="true"
    >
        <circle cx="12" cy="12" r="10" />
        <path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20" />
    </svg>
</template>
