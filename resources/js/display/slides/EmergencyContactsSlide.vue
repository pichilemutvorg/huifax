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

        <ul class="grid grid-cols-3 gap-10">
            <li v-for="contact in data.contacts" :key="contact.id" class="card bg-base-200">
                <div class="card-body items-center gap-4 py-10 text-center">
                    <p class="text-8xl leading-none font-black text-accent tabular-nums">{{ contact.phone }}</p>
                    <p class="text-4xl font-semibold">{{ contact.name }}</p>
                </div>
            </li>
        </ul>
    </section>
</template>
