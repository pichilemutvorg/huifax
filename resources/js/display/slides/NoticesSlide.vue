<script setup>
defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
});
</script>

<template>
    <section class="hx-notice flex h-full flex-col justify-center">
        <p class="hx-eyebrow self-start">{{ data.heading }}</p>
        <h2 class="hx-title mt-6 line-clamp-2">{{ data.title }}</h2>
        <p v-if="data.body" class="hx-body mt-10 line-clamp-5 max-w-[1500px]">{{ data.body }}</p>
    </section>
</template>
