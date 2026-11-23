<script setup>
defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
});
</script>

<template>
    <section class="flex h-full flex-col">
        <p class="hx-eyebrow self-start">{{ data.heading }}</p>

        <div class="hx-featured mt-8 grid min-h-0 flex-1 grid-cols-5 items-center gap-16">
            <div class="hx-media col-span-3 aspect-video overflow-hidden">
                <img :src="data.image" alt="" class="size-full object-cover" />
            </div>

            <div class="hx-featured-text col-span-2 flex min-w-0 flex-col items-start gap-6">
                <span v-if="data.label" class="hx-badge">{{ data.label }}</span>
                <h2 class="hx-title hx-title-sm line-clamp-3">{{ data.name }}</h2>
                <p v-if="data.tagline" class="hx-body hx-body-sm line-clamp-3">{{ data.tagline }}</p>
                <p v-if="data.schedule" class="hx-highlight mt-4">{{ data.schedule }}</p>
            </div>
        </div>
    </section>
</template>
