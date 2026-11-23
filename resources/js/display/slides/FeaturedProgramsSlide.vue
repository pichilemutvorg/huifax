<script setup>
defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
});
</script>

<template>
    <section class="flex h-full flex-col">
        <p class="text-4xl font-semibold tracking-wide text-primary uppercase">{{ data.heading }}</p>

        <div class="mt-8 grid min-h-0 flex-1 grid-cols-5 items-center gap-16">
            <div class="col-span-3 aspect-video overflow-hidden rounded-box bg-base-200 shadow-2xl">
                <img :src="data.image" alt="" class="size-full object-cover" />
            </div>

            <div class="col-span-2 flex min-w-0 flex-col items-start gap-6">
                <span v-if="data.label" class="badge px-6 py-6 text-3xl badge-xl font-bold badge-accent">
                    {{ data.label }}
                </span>
                <h2 class="line-clamp-3 text-7xl leading-tight font-black">{{ data.name }}</h2>
                <p v-if="data.tagline" class="line-clamp-3 text-4xl leading-snug text-base-content/80">
                    {{ data.tagline }}
                </p>
                <p v-if="data.schedule" class="mt-4 text-5xl font-bold text-accent">{{ data.schedule }}</p>
            </div>
        </div>
    </section>
</template>
