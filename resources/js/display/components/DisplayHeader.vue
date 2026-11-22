<script setup>
import { useClock } from '../composables/useClock.js';

const props = defineProps({
    settings: { type: Object, required: true },
});

const { time, date } = useClock(
    () => props.settings.locale,
    () => props.settings.timezone,
);
</script>

<template>
    <header class="flex h-36 items-center justify-between gap-12">
        <div class="flex min-w-0 items-center gap-8">
            <img v-if="settings.logo_url" :src="settings.logo_url" alt="" class="h-28 w-auto object-contain" />
            <p v-else class="truncate text-6xl font-black tracking-tight">{{ settings.title }}</p>
        </div>

        <div class="text-right">
            <p class="text-7xl leading-none font-bold tabular-nums">{{ time }}</p>
            <p class="mt-3 text-3xl text-base-content/80 first-letter:uppercase">{{ date }}</p>
        </div>
    </header>
</template>
