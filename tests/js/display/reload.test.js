import { describe, expect, it } from 'vitest';
import {
    MIN_UPTIME_MINUTES,
    RELOAD_WINDOW_MINUTES,
    reloadUrl,
    shouldReload,
} from '../../../resources/js/display/lib/reload.js';

const uptime = MIN_UPTIME_MINUTES + 1;

describe('shouldReload', () => {
    it('is disabled without a configured time', () => {
        expect(shouldReload('05:00', null, uptime)).toBe(false);
        expect(shouldReload('05:00', '', uptime)).toBe(false);
    });

    it('reloads at the configured time and within the window', () => {
        expect(shouldReload('05:00', '05:00', uptime)).toBe(true);
        expect(shouldReload('05:29', '05:00', uptime)).toBe(true);
    });

    it('does not reload before or after the window', () => {
        expect(shouldReload('04:59', '05:00', uptime)).toBe(false);
        expect(shouldReload(`05:${RELOAD_WINDOW_MINUTES}`, '05:00', uptime)).toBe(false);
    });

    it('handles windows that cross midnight', () => {
        expect(shouldReload('00:10', '23:50', uptime)).toBe(true);
        expect(shouldReload('23:49', '23:50', uptime)).toBe(false);
    });

    it('does not reload a page that just loaded', () => {
        expect(shouldReload('05:00', '05:00', MIN_UPTIME_MINUTES - 1)).toBe(false);
    });

    it('ignores malformed times', () => {
        expect(shouldReload('05:00', '5am', uptime)).toBe(false);
        expect(shouldReload('25:00', '05:00', uptime)).toBe(false);
    });
});

describe('reloadUrl', () => {
    it('adds a fresh reload parameter so the navigation targets a new URL', () => {
        expect(reloadUrl('http://display.test/', 123)).toBe('http://display.test/?reload=123');
    });

    it('replaces the previous reload parameter and keeps the others', () => {
        expect(reloadUrl('http://display.test/?lang=es&reload=1&guides', 2)).toBe(
            'http://display.test/?lang=es&reload=2&guides=',
        );
    });

    it('drops the fragment', () => {
        expect(reloadUrl('http://display.test/#x', 5)).toBe('http://display.test/?reload=5');
    });
});
