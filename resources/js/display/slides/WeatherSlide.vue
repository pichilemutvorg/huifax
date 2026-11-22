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

        <div class="grid min-h-0 flex-1 grid-cols-5 gap-10">
            <div class="card col-span-2 bg-base-200">
                <div class="card-body items-center justify-center gap-4 text-center">
                    <p class="text-4xl font-semibold">
                        {{ strings.today }}
                        <span v-if="todayCondition?.period" class="text-base-content/70">
                            · {{ strings.periods?.[todayCondition.period] }}
                        </span>
                    </p>
                    <img v-if="todayCondition?.icon" :src="todayCondition.icon" alt="" class="size-48" />
                    <p class="line-clamp-3 text-4xl leading-snug">{{ todayCondition?.text }}</p>
                    <p v-if="today.min !== null || today.max !== null" class="mt-2 text-6xl font-black tabular-nums">
                        <span class="text-info">{{ temperature(today.min) }}</span>
                        <span class="text-base-content/50"> / </span>
                        <span class="text-accent">{{ temperature(today.max) }}</span>
                    </p>
                </div>
            </div>

            <ul class="col-span-3 grid grid-cols-2 grid-rows-2 gap-10">
                <li v-for="day in nextDays" :key="day.date" class="card bg-base-200">
                    <div class="card-body flex-row items-center gap-6 py-6">
                        <img v-if="day.condition?.icon" :src="day.condition.icon" alt="" class="size-28 shrink-0" />
                        <div class="min-w-0">
                            <p class="text-4xl font-semibold first-letter:uppercase">{{ dayName(day.date) }}</p>
                            <p class="mt-2 text-5xl font-black tabular-nums">
                                <span class="text-info">{{ temperature(day.min) }}</span>
                                <span class="text-base-content/50"> / </span>
                                <span class="text-accent">{{ temperature(day.max) }}</span>
                            </p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <p class="mt-6 text-2xl text-base-content/70">{{ data.attribution }}</p>
    </section>
</template>
