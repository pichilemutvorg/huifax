<script setup>
defineProps({
    settings: { type: Object, required: true },
});
</script>

<template>
    <section class="flex h-full flex-col items-center justify-center text-center">
        <img v-if="settings.logo_url" :src="settings.logo_url" alt="" class="max-h-80 w-auto object-contain" />
        <p v-else class="text-9xl font-black tracking-tight">{{ settings.title }}</p>
        <p v-if="settings.tagline" class="mt-10 text-5xl text-base-content/80">{{ settings.tagline }}</p>
    </section>
</template>
