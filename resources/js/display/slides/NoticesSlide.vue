<script setup>
defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
});
</script>

<template>
    <section class="flex h-full flex-col justify-center">
        <p class="text-4xl font-semibold tracking-wide text-primary uppercase">{{ data.heading }}</p>
        <h2 class="mt-6 line-clamp-2 text-8xl leading-tight font-black">{{ data.title }}</h2>
        <p v-if="data.body" class="mt-10 line-clamp-5 max-w-[1500px] text-5xl leading-snug text-base-content/90">
            {{ data.body }}
        </p>
    </section>
</template>
