import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createRadioPlayer, retryDelay } from '../../../resources/js/display/lib/radio.js';

/** Minimal stand-in for HTMLAudioElement. */
class FakeAudio extends EventTarget {
    src = '';
    volume = 1;
    currentTime = 0;
    error = null;
    plays = [];
    playResult = () => Promise.resolve();

    play() {
        this.plays.push(this.src);
        return this.playResult();
    }
    pause() {}
    load() {}
    removeAttribute(name) {
        if (name === 'src') this.src = '';
    }
    emit(type) {
        this.dispatchEvent(new Event(type));
    }
}

const URL = 'https://radio.example/stream';

describe('createRadioPlayer', () => {
    let audio;
    let player;

    beforeEach(() => {
        vi.useFakeTimers();
        audio = new FakeAudio();
        player = createRadioPlayer({ createAudio: () => audio, log: () => {} });
    });

    afterEach(() => {
        player.destroy();
        vi.useRealTimers();
    });

    it('plays the stream when enabled, with the configured volume', () => {
        player.configure({ enabled: true, stream_url: URL, volume: 40 });

        expect(audio.plays).toEqual([URL]);
        expect(audio.volume).toBe(0.4);
    });

    it('stays silent when disabled or without a URL', () => {
        player.configure({ enabled: false, stream_url: URL, volume: 80 });
        player.configure({ enabled: true, stream_url: null, volume: 80 });

        expect(audio.plays).toEqual([]);
    });

    it('does not restart when the same settings arrive again', () => {
        player.configure({ enabled: true, stream_url: URL, volume: 80 });
        player.configure({ enabled: true, stream_url: URL, volume: 60 });

        expect(audio.plays).toHaveLength(1);
        expect(audio.volume).toBe(0.6);
    });

    it('stops and releases the stream when disabled', () => {
        player.configure({ enabled: true, stream_url: URL, volume: 80 });
        player.configure({ enabled: false, stream_url: URL, volume: 80 });

        expect(audio.src).toBe('');
    });

    it('reconnects after errors with a growing delay, reset once playing', () => {
        player.configure({ enabled: true, stream_url: URL, volume: 80 });

        audio.emit('error');
        expect(audio.src).toBe('');
        vi.advanceTimersByTime(1999);
        expect(audio.plays).toHaveLength(1);
        vi.advanceTimersByTime(1);
        expect(audio.plays).toHaveLength(2);

        audio.emit('error');
        vi.advanceTimersByTime(3999);
        expect(audio.plays).toHaveLength(2);
        vi.advanceTimersByTime(1);
        expect(audio.plays).toHaveLength(3);

        audio.emit('playing');
        audio.emit('ended');
        vi.advanceTimersByTime(2000);
        expect(audio.plays).toHaveLength(4);
    });

    it('retries when play() is rejected (e.g. autoplay blocked)', async () => {
        audio.playResult = () => Promise.reject(new DOMException('blocked', 'NotAllowedError'));
        player.configure({ enabled: true, stream_url: URL, volume: 80 });
        await Promise.resolve();

        vi.advanceTimersByTime(2000);
        expect(audio.plays).toHaveLength(2);
    });

    it('reconnects when playback stops advancing', () => {
        player.configure({ enabled: true, stream_url: URL, volume: 80 });

        audio.currentTime = 5;
        vi.advanceTimersByTime(10_000); // progress seen
        vi.advanceTimersByTime(10_000); // strike 1
        expect(audio.plays).toHaveLength(1);
        vi.advanceTimersByTime(10_000); // strike 2: reconnect
        vi.advanceTimersByTime(2_000);
        expect(audio.plays).toHaveLength(2);
    });

    it('cancels a pending retry when stopped', () => {
        player.configure({ enabled: true, stream_url: URL, volume: 80 });
        audio.emit('error');
        player.stop();

        vi.advanceTimersByTime(120_000);
        expect(audio.plays).toHaveLength(1);
    });
});

describe('retryDelay', () => {
    it('doubles up to the maximum', () => {
        expect([0, 1, 2, 3, 4, 5, 6].map((n) => retryDelay(n, 2, 60))).toEqual([2, 4, 8, 16, 32, 60, 60]);
    });
});
