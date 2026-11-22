import { describe, expect, it } from 'vitest';
import { normalizePayload } from '../../../resources/js/display/lib/payload.js';

const known = new Set(['notices']);
const slide = (overrides = {}) => ({ id: '1:a', type: 'notices', duration: 10, data: {}, ...overrides });

describe('normalizePayload', () => {
    it.each([null, 'text', [], {}, { settings: {} }, { slides: [] }, { settings: [], slides: [] }])(
        'rejects an unusable payload: %j',
        (payload) => {
            expect(normalizePayload(payload, known)).toBeNull();
        },
    );

    it('keeps valid slides of known types', () => {
        const result = normalizePayload({ settings: {}, strings: { a: 'b' }, slides: [slide()] }, known);

        expect(result.slides).toHaveLength(1);
        expect(result.strings).toEqual({ a: 'b' });
    });

    it('drops malformed slides and unknown types', () => {
        const result = normalizePayload(
            {
                settings: {},
                slides: [
                    slide({ id: 'ok' }),
                    slide({ type: 'weather' }),
                    slide({ duration: 0 }),
                    slide({ duration: '10' }),
                    slide({ data: null }),
                    slide({ id: 3 }),
                    'garbage',
                ],
            },
            known,
        );

        expect(result.slides.map((s) => s.id)).toEqual(['ok']);
    });

    it('defaults missing strings to an empty object', () => {
        expect(normalizePayload({ settings: {}, slides: [] }, known).strings).toEqual({});
    });

    it('keeps valid backgrounds and normalizes slide backgrounds', () => {
        const result = normalizePayload(
            {
                settings: {},
                backgrounds: [{ id: 1, url: '/a.jpg' }, { id: 2 }, { id: 3, url: '' }, null],
                slides: [slide({ id: 'with', background: '/own.jpg' }), slide({ id: 'without', background: 42 })],
            },
            known,
        );

        expect(result.backgrounds).toEqual([{ id: 1, url: '/a.jpg' }]);
        expect(result.slides.map((s) => s.background)).toEqual(['/own.jpg', null]);
    });

    it('defaults missing backgrounds to an empty list', () => {
        expect(normalizePayload({ settings: {}, slides: [] }, known).backgrounds).toEqual([]);
    });
});
