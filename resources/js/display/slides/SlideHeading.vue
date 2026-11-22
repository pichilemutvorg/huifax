<script setup>
import { translate } from '../lib/strings.js';

defineProps({
    heading: { type: String, required: true },
    page: { type: Number, default: 1 },
    pages: { type: Number, default: 1 },
    strings: { type: Object, required: true },
});
</script>

<template>
    <div class="mb-12 flex items-baseline justify-between gap-8">
        <h2 class="text-6xl font-bold text-primary">{{ heading }}</h2>
        <p v-if="pages > 1" class="text-3xl text-base-content/60 tabular-nums">
            {{ translate(strings.page_of, { current: page, total: pages }) }}
        </p>
    </div>
</template>
