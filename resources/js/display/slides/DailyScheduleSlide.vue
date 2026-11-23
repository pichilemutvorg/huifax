<script setup>
import { dayLabel } from '../lib/schedule.js';
import AiringRow from './AiringRow.vue';
import SlideHeading from './SlideHeading.vue';

const props = defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, required: true },
});

const day = dayLabel(props.data.date, new Date(), {
    locale: props.settings.locale,
    timeZone: props.settings.timezone,
    today: props.strings.today,
    tomorrow: props.strings.tomorrow,
});
</script>

<template>
    <section class="flex h-full flex-col">
        <SlideHeading :heading="`${data.heading} · ${day}`" :page="data.page" :pages="data.pages" :strings="strings" />

        <ul class="flex min-h-0 flex-1 flex-col gap-3">
            <AiringRow
                v-for="airing in data.airings"
                :key="`${airing.starts_at}-${airing.program}`"
                :airing="airing"
                :strings="strings"
                :settings="settings"
            />
        </ul>
    </section>
</template>
