import { onBeforeUnmount, onMounted, watch } from 'vue';
import { createRadioPlayer } from '../lib/radio.js';

/**
 * Plays the instance's radio stream when enabled in the admin panel.
 * Off by default: usually the playout system (e.g. CasparCG) plays the audio.
 *
 * @param {() => { enabled?: boolean, stream_url?: string | null, volume?: number } | undefined} settings
 */
export function useRadio(settings) {
    let player = null;

    onMounted(() => {
        player = createRadioPlayer();
        player.configure(settings() ?? {});
    });

    watch(settings, (value) => player?.configure(value ?? {}), { deep: true });

    onBeforeUnmount(() => player?.destroy());
}
