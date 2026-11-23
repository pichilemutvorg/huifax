<script setup>
defineProps({
    settings: { type: Object, required: true },
});
</script>

<template>
    <footer class="hx-footer flex items-end">
        <p v-if="settings.tagline" class="hx-tagline truncate">{{ settings.tagline }}</p>
    </footer>
</template>
