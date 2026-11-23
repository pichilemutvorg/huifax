<script setup>
/**
 * Broadcast safe area guides (SMPTE ST 2046-1) drawn over the stage:
 * - action safe, 93%: anything important must stay inside;
 * - title safe, 90%: text and graphics should stay inside.
 * The display's own content margins sit on the title safe area.
 */
</script>

<template>
    <div class="pointer-events-none absolute inset-0 z-50" aria-hidden="true">
        <div class="absolute inset-[3.5%] border-2 border-dashed border-info"></div>
        <div class="absolute inset-[5%] border-2 border-warning"></div>
        <!-- Labels in the outer margin, clear of the content. -->
        <p class="absolute top-0 left-[3.5%] flex h-[3.5%] items-center gap-6 font-mono text-xl">
            <span class="text-info">- - action safe 93%</span>
            <span class="text-warning">── title safe 90%</span>
        </p>
        <div class="absolute top-1/2 left-1/2 h-16 w-0.5 -translate-1/2 bg-error"></div>
        <div class="absolute top-1/2 left-1/2 h-0.5 w-16 -translate-1/2 bg-error"></div>
    </div>
</template>
