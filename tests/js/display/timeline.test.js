import { describe, expect, it } from 'vitest';
import { formatClock, timeline } from '../../../resources/js/display/lib/timeline.js';

describe('timeline', () => {
    it('places each slide after the previous one and sums the cycle', () => {
        const { items, total } = timeline([{ duration: 20 }, { duration: 15 }, { duration: 45 }]);

        expect(items.map(({ start, end }) => [start, end])).toEqual([
            [0, 20],
            [20, 35],
            [35, 80],
        ]);
        expect(total).toBe(80);
    });

    it('handles an empty rotation', () => {
        expect(timeline([])).toEqual({ items: [], total: 0 });
    });
});

describe('formatClock', () => {
    it.each([
        [0, '0:00'],
        [5, '0:05'],
        [130, '2:10'],
        [3725, '1:02:05'],
    ])('%i seconds is %s', (seconds, expected) => {
        expect(formatClock(seconds)).toBe(expected);
    });
});
