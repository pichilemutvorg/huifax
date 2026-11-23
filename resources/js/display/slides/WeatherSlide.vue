<script setup>
import { computed } from 'vue';
import { conditionFor, periodForHour } from '../lib/weather.js';
import SlideHeading from './SlideHeading.vue';

const props = defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, required: true },
});

// Fixed when the slide goes on air: the slide never changes while on screen.
const hour = Number(
    new Intl.DateTimeFormat('en-GB', {
        timeZone: props.settings.timezone || undefined,
        hour: '2-digit',
        hourCycle: 'h23',
    }).format(new Date()),
);

const [today, ...upcoming] = props.data.days;
const todayCondition = conditionFor(today, periodForHour(hour));

const weekday = new Intl.DateTimeFormat(props.settings.locale || undefined, { weekday: 'long', timeZone: 'UTC' });
const dayName = (date) => weekday.format(new Date(`${date}T12:00:00Z`));

const nextDays = computed(() =>
    upcoming.slice(0, 4).map((day) => ({ ...day, condition: conditionFor(day, 'afternoon') })),
);

const temperature = (value) => (value === null || value === undefined ? '–' : `${value}°`);
</script>

<template>
    <section class="flex h-full flex-col">
        <SlideHeading :heading="`${data.heading} · ${data.location}`" :strings="strings" />

        <div class="hx-grid grid min-h-0 flex-1 grid-cols-5">
            <div class="hx-card hx-card-feature col-span-2">
                <div class="hx-card-body hx-feature-body">
                    <p class="hx-label text-4xl">
                        {{ strings.today }}
                        <span v-if="todayCondition?.period" class="hx-muted">
                            · {{ strings.periods?.[todayCondition.period] }}
                        </span>
                    </p>
                    <img v-if="todayCondition?.icon" :src="todayCondition.icon" alt="" class="size-48" />
                    <p class="hx-value line-clamp-3 text-4xl leading-snug">{{ todayCondition?.text }}</p>
                    <p v-if="today.min !== null || today.max !== null" class="hx-figure mt-2 text-6xl tabular-nums">
                        <span class="hx-min">{{ temperature(today.min) }}</span>
                        <span class="hx-muted"> / </span>
                        <span class="hx-max">{{ temperature(today.max) }}</span>
                    </p>
                </div>
            </div>

            <ul class="hx-grid col-span-3 grid grid-cols-2 grid-rows-2">
                <li v-for="day in nextDays" :key="day.date" class="hx-card">
                    <div class="hx-card-body hx-day-body">
                        <img v-if="day.condition?.icon" :src="day.condition.icon" alt="" class="size-28 shrink-0" />
                        <div class="min-w-0">
                            <p class="hx-label text-4xl first-letter:uppercase">{{ dayName(day.date) }}</p>
                            <p class="hx-figure mt-2 text-5xl tabular-nums">
                                <span class="hx-min">{{ temperature(day.min) }}</span>
                                <span class="hx-muted"> / </span>
                                <span class="hx-max">{{ temperature(day.max) }}</span>
                            </p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <p class="hx-caption mt-6">{{ data.attribution }}</p>
    </section>
</template>
