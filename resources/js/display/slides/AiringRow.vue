<script setup>
import { formatTime } from '../lib/schedule.js';

defineProps({
    airing: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, required: true },
    // Day column: undefined hides it, an empty string keeps it blank.
    day: { type: String, default: undefined },
});

const tagClasses = {
    live: 'badge-error',
    premiere: 'badge-accent',
    rerun: 'badge-outline',
};
</script>

<template>
    <li class="flex items-center gap-10 rounded-box bg-base-200 px-10 py-4">
        <p
            v-if="day !== undefined"
            class="w-56 shrink-0 truncate text-3xl font-semibold text-base-content/70 first-letter:uppercase"
        >
            {{ day }}
        </p>
        <p class="w-36 shrink-0 text-5xl font-black text-accent tabular-nums">
            {{ formatTime(airing.starts_at, settings.locale, settings.timezone) }}
        </p>
        <p class="min-w-0 flex-1 truncate text-4xl">
            <span class="font-semibold">{{ airing.program }}</span>
            <span v-if="airing.description" class="text-3xl text-base-content/70"> · {{ airing.description }}</span>
        </p>
        <span
            v-if="airing.tag"
            class="badge shrink-0 px-5 py-5 text-2xl badge-xl font-semibold"
            :class="tagClasses[airing.tag] ?? 'badge-outline'"
        >
            {{ strings.tags?.[airing.tag] ?? airing.tag }}
        </span>
    </li>
</template>
