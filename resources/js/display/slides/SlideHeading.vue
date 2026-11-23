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
    <div class="hx-heading-row flex items-baseline justify-between gap-8">
        <h2 class="hx-heading">{{ heading }}</h2>
        <p v-if="pages > 1" class="hx-pager tabular-nums">
            {{ translate(strings.page_of, { current: page, total: pages }) }}
        </p>
    </div>
</template>
