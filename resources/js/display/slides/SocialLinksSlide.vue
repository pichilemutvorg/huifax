<script setup>
import SocialIcon from '../components/SocialIcon.vue';
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

        <ul class="grid grid-cols-2 gap-10">
            <li v-for="link in data.links" :key="link.id" class="card bg-base-200">
                <div class="card-body flex-row items-center gap-8 py-8">
                    <SocialIcon :platform="link.platform" class="size-24 shrink-0 text-accent" />
                    <div class="min-w-0">
                        <p class="truncate text-5xl font-bold">{{ link.handle }}</p>
                        <p class="mt-2 text-3xl text-base-content/70">{{ link.platform_label }}</p>
                    </div>
                </div>
            </li>
        </ul>
    </section>
</template>
