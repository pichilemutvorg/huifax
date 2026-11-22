<script setup>
defineProps({
    settings: { type: Object, required: true },
});
</script>

<template>
    <footer class="flex h-16 items-end">
        <p v-if="settings.tagline" class="truncate text-3xl text-base-content/70">{{ settings.tagline }}</p>
    </footer>
</template>
