<script setup>
import { dateIn, dayLabel, upcomingAirings } from '../lib/schedule.js';
import AiringRow from './AiringRow.vue';
import SlideHeading from './SlideHeading.vue';

const props = defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, required: true },
});

// Fixed when the slide goes on air. Airings that started since the content
// was fetched are dropped; the server sends a few spare ones to fill in.
const now = new Date();
const options = {
    locale: props.settings.locale,
    timeZone: props.settings.timezone,
    today: props.strings.today,
    tomorrow: props.strings.tomorrow,
};

// The day is only written on the first airing of each day.
const rows = upcomingAirings(props.data.airings, now, props.data.count).map((airing, index, list) => {
    const date = dateIn(new Date(airing.starts_at), props.settings.timezone);
    const previous = index > 0 ? dateIn(new Date(list[index - 1].starts_at), props.settings.timezone) : null;

    return { airing, day: date === previous ? '' : dayLabel(date, now, options) };
});
</script>

<template>
    <section class="flex h-full flex-col">
        <SlideHeading :heading="data.heading" :strings="strings" />

        <ul class="flex min-h-0 flex-1 flex-col gap-3">
            <AiringRow
                v-for="row in rows"
                :key="`${row.airing.starts_at}-${row.airing.program}`"
                :airing="row.airing"
                :day="row.day"
                :strings="strings"
                :settings="settings"
            />
        </ul>
    </section>
</template>
