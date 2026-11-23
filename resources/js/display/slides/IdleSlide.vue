<script setup>
defineProps({
    settings: { type: Object, required: true },
});
</script>

<template>
    <section class="flex h-full flex-col items-center justify-center text-center">
        <img
            v-if="settings.logo_url"
            :src="settings.logo_url"
            alt=""
            class="hx-idle-logo max-h-80 w-auto object-contain"
        />
        <p v-else class="hx-title text-9xl">{{ settings.title }}</p>
        <p v-if="settings.tagline" class="hx-body mt-10">{{ settings.tagline }}</p>
    </section>
</template>
