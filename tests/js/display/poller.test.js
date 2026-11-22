import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createPoller, nextDelay } from '../../../resources/js/display/lib/poller.js';

const response = (status, body = null, etag = null) => ({
    status,
    ok: status >= 200 && status < 300,
    headers: { get: (name) => (name === 'ETag' ? etag : null) },
    json: async () => body,
});

describe('nextDelay', () => {
    it('uses the interval while healthy', () => {
        expect(nextDelay(30, 0, 300)).toBe(30);
    });

    it('backs off exponentially up to the limit', () => {
        expect(nextDelay(30, 1, 300)).toBe(60);
        expect(nextDelay(30, 2, 300)).toBe(120);
        expect(nextDelay(30, 5, 300)).toBe(300);
    });

    it('falls back to a sane interval', () => {
        expect(nextDelay(undefined, 0, 300)).toBe(30);
        expect(nextDelay(-5, 0, 300)).toBe(30);
    });
});

describe('createPoller', () => {
    beforeEach(() => vi.useFakeTimers());
    afterEach(() => vi.useRealTimers());

    it('sends the known ETag and ignores 304 responses', async () => {
        const fetch = vi.fn().mockResolvedValue(response(304));
        const onContent = vi.fn();
        const poller = createPoller({ url: '/api', etag: '"v1"', interval: () => 30, onContent, fetch });

        poller.start();
        await vi.advanceTimersByTimeAsync(30_000);

        expect(fetch).toHaveBeenCalledTimes(1);
        expect(fetch.mock.calls[0][1].headers['If-None-Match']).toBe('"v1"');
        expect(onContent).not.toHaveBeenCalled();
        poller.stop();
    });

    it('delivers new content and remembers its ETag', async () => {
        const fetch = vi
            .fn()
            .mockResolvedValueOnce(response(200, { slides: [] }, '"v2"'))
            .mockResolvedValue(response(304));
        const onContent = vi.fn();
        const poller = createPoller({ url: '/api', etag: '"v1"', interval: () => 30, onContent, fetch });

        poller.start();
        await vi.advanceTimersByTimeAsync(60_000);

        expect(onContent).toHaveBeenCalledWith({ slides: [] }, '"v2"');
        expect(fetch.mock.calls[1][1].headers['If-None-Match']).toBe('"v2"');
        poller.stop();
    });

    it('backs off on network errors and recovers', async () => {
        const fetch = vi
            .fn()
            .mockRejectedValueOnce(new TypeError('offline'))
            .mockRejectedValueOnce(new TypeError('offline'))
            .mockResolvedValue(response(304));
        const poller = createPoller({ url: '/api', etag: null, interval: () => 30, onContent: vi.fn(), fetch });

        poller.start();
        await vi.advanceTimersByTimeAsync(30_000); // 1st poll fails → next in 60 s
        expect(fetch).toHaveBeenCalledTimes(1);
        await vi.advanceTimersByTimeAsync(60_000); // 2nd fails → next in 120 s
        expect(fetch).toHaveBeenCalledTimes(2);
        await vi.advanceTimersByTimeAsync(119_000);
        expect(fetch).toHaveBeenCalledTimes(2);
        await vi.advanceTimersByTimeAsync(1_000); // 3rd succeeds → back to 30 s
        expect(fetch).toHaveBeenCalledTimes(3);
        await vi.advanceTimersByTimeAsync(30_000);
        expect(fetch).toHaveBeenCalledTimes(4);
        poller.stop();
    });

    it('treats server errors and invalid JSON as failures without throwing', async () => {
        const fetch = vi
            .fn()
            .mockResolvedValueOnce(response(500))
            .mockResolvedValueOnce({ ...response(200), json: async () => JSON.parse('{') });
        const onContent = vi.fn();
        const poller = createPoller({ url: '/api', etag: null, interval: () => 30, onContent, fetch });

        poller.start();
        await vi.advanceTimersByTimeAsync(30_000 + 60_000);

        expect(fetch).toHaveBeenCalledTimes(2);
        expect(onContent).not.toHaveBeenCalled();
        poller.stop();
    });

    it('stops polling when stopped', async () => {
        const fetch = vi.fn().mockResolvedValue(response(304));
        const poller = createPoller({ url: '/api', etag: null, interval: () => 30, onContent: vi.fn(), fetch });

        poller.start();
        poller.stop();
        await vi.advanceTimersByTimeAsync(120_000);

        expect(fetch).not.toHaveBeenCalled();
    });
});
