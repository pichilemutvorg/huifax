<script setup>
import SlideHeading from './SlideHeading.vue';

defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
});
</script>

<template>
    <section class="flex h-full flex-col">
        <SlideHeading :heading="data.heading" :page="data.page" :pages="data.pages" :strings="strings" />

        <!-- Sizes in cqh scale with each card, so any number of rows fits. -->
        <div class="hx-fit">
            <ul class="hx-grid grid grid-cols-3" :style="{ '--rows': Math.ceil(data.contacts.length / 3) }">
                <li v-for="contact in data.contacts" :key="contact.id" class="hx-card hx-tile">
                    <div class="hx-card-body hx-tile-body">
                        <p class="hx-figure text-[min(6rem,36cqh)] leading-none tabular-nums">{{ contact.phone }}</p>
                        <p class="hx-label line-clamp-2 text-[min(2.25rem,13cqh)] leading-tight">{{ contact.name }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
