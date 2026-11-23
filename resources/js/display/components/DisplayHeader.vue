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

<!-- Visual style comes from the theme (hx-* classes, see resources/css/themes). -->
<template>
    <header class="hx-header flex items-center justify-between">
        <div class="hx-brand flex min-w-0 items-center">
            <img v-if="settings.logo_url" :src="settings.logo_url" alt="" class="hx-logo w-auto object-contain" />
            <p v-else class="hx-brand-name truncate">{{ settings.title }}</p>
        </div>

        <div class="hx-datetime">
            <p class="hx-clock tabular-nums">{{ time }}</p>
            <p class="hx-date">{{ date }}</p>
        </div>
    </header>
</template>
