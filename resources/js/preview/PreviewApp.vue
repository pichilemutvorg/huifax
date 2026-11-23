<script setup>
import { computed } from 'vue';
import BackgroundLayer from '../display/components/BackgroundLayer.vue';
import DisplayFooter from '../display/components/DisplayFooter.vue';
import DisplayHeader from '../display/components/DisplayHeader.vue';
import { formatClock, timeline } from '../display/lib/timeline.js';
import { normalizePayload } from '../display/lib/payload.js';
import { translate } from '../display/lib/strings.js';
import { slideComponents } from '../display/slides/index.js';
import ScaledStage from './ScaledStage.vue';

const props = defineProps({
    payload: { type: Object, required: true },
    names: { type: Object, required: true },
    strings: { type: Object, required: true },
});

const content = normalizePayload(props.payload, new Set(Object.keys(slideComponents))) ?? {
    settings: {},
    strings: {},
    backgrounds: [],
    slides: [],
};

const plan = computed(() => timeline(content.slides));

// The general rotation runs on its own clock; show its first photo.
const backgroundFor = (slide) => slide.background ?? content.backgrounds[0]?.url ?? null;
const nameFor = (slide) => props.names[slide.id.split(':')[0]] ?? slide.type;
</script>

<template>
    <main class="hx-preview-page min-h-screen p-8">
        <p class="mb-6 text-lg">
            {{ translate(strings.slides, { count: plan.items.length }) }} ·
            {{ translate(strings.cycle, { time: formatClock(plan.total) }) }}
        </p>

        <p v-if="plan.items.length === 0" class="text-lg opacity-70">{{ strings.empty }}</p>

        <ol class="grid grid-cols-[repeat(auto-fill,minmax(22rem,1fr))] gap-6">
            <li v-for="(item, index) in plan.items" :key="item.slide.id" class="hx-preview-item">
                <!-- Static: no entry animations, so every thumbnail shows its final state. -->
                <ScaledStage class="hx-static">
                    <BackgroundLayer :url="backgroundFor(item.slide)" />
                    <div class="relative flex h-full flex-col px-24 py-14">
                        <DisplayHeader :settings="content.settings" />
                        <div class="min-h-0 flex-1 py-12">
                            <component
                                :is="slideComponents[item.slide.type]"
                                :data="item.slide.data"
                                :strings="content.strings"
                                :settings="content.settings"
                            />
                        </div>
                        <DisplayFooter :settings="content.settings" />
                    </div>
                </ScaledStage>

                <div class="flex items-baseline gap-3 px-1 pt-3 text-base">
                    <span class="font-bold tabular-nums">{{ index + 1 }}</span>
                    <span class="min-w-0 flex-1 truncate">
                        {{ nameFor(item.slide) }}
                        <template v-if="item.slide.data.pages > 1">
                            ·
                            {{ translate(strings.page, { page: item.slide.data.page, pages: item.slide.data.pages }) }}
                        </template>
                    </span>
                    <span class="tabular-nums opacity-70">
                        {{ formatClock(item.start) }} ·
                        {{ translate(strings.seconds, { seconds: item.slide.duration }) }}
                    </span>
                </div>
            </li>
        </ol>
    </main>
</template>
