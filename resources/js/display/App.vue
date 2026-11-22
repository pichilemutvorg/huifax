<script setup>
// Phase 0 skeleton. The carousel, header and slides arrive in Phase 1.
defineProps({
    locale: { type: String, required: true },
    timezone: { type: String, required: true },
});
</script>

<template>
    <main class="flex h-full w-full items-center justify-center bg-base-100 text-base-content">
        <h1 class="text-8xl font-bold">HUIFAX</h1>
    </main>
</template>
