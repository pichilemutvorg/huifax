<script setup>
import SocialIcon from '../components/SocialIcon.vue';
import { socialIcons } from '../icons/socialIcons.js';
import SlideHeading from './SlideHeading.vue';

defineProps({
    data: { type: Object, required: true },
    strings: { type: Object, required: true },
    settings: { type: Object, default: () => ({}) },
});

// Platform colour, for themes that paint each network in its own colour.
const brandColor = (platform) => (socialIcons[platform] ? `#${socialIcons[platform].hex}` : null);
</script>

<template>
    <section class="flex h-full flex-col">
        <SlideHeading :heading="data.heading" :page="data.page" :pages="data.pages" :strings="strings" />

        <!-- Sizes in cqh scale with each card, so any number of rows fits. -->
        <div class="hx-fit">
            <ul
                class="hx-grid grid grid-cols-2"
                :style="{ '--rows': Math.ceil(data.links.length / 2), '--hx-row-max': '13rem' }"
            >
                <li
                    v-for="link in data.links"
                    :key="link.id"
                    class="hx-card hx-brand-tile"
                    :style="brandColor(link.platform) ? { '--hx-brand': brandColor(link.platform) } : {}"
                >
                    <div class="hx-card-body hx-brand-tile-body">
                        <SocialIcon :platform="link.platform" class="hx-icon size-[min(6rem,45cqh)] shrink-0" />
                        <div class="min-w-0">
                            <p class="hx-value line-clamp-2 text-[min(3rem,20cqh)] leading-tight wrap-anywhere">
                                {{ link.handle }}
                            </p>
                            <p class="hx-label mt-2 text-[min(1.875rem,13cqh)]">{{ link.platform_label }}</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>
