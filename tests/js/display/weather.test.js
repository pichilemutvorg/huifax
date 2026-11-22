import { describe, expect, it } from 'vitest';
import { conditionFor, periodForHour } from '../../../resources/js/display/lib/weather.js';

const period = (name) => ({ period: name, icon: `/${name}.png`, text: name });

describe('periodForHour', () => {
    it.each([
        [0, 'dawn'],
        [5, 'dawn'],
        [6, 'morning'],
        [11, 'morning'],
        [12, 'afternoon'],
        [17, 'afternoon'],
        [18, 'night'],
        [23, 'night'],
    ])('hour %i is %s', (hour, expected) => {
        expect(periodForHour(hour)).toBe(expected);
    });
});

describe('conditionFor', () => {
    it('uses the preferred period when present', () => {
        const day = { summary: null, periods: ['dawn', 'morning', 'afternoon', 'night'].map(period) };
        expect(conditionFor(day, 'afternoon').period).toBe('afternoon');
    });

    it('falls forward, then backward, to the closest period', () => {
        expect(conditionFor({ summary: null, periods: [period('night')] }, 'morning').period).toBe('night');
        expect(conditionFor({ summary: null, periods: [period('dawn')] }, 'afternoon').period).toBe('dawn');
    });

    it('uses the summary for rest-of-day forecasts', () => {
        const day = { summary: { icon: '/wind.png', text: 'Viento' }, periods: [] };
        expect(conditionFor(day, 'night')).toEqual({ period: null, icon: '/wind.png', text: 'Viento' });
    });

    it('returns null when there is nothing to show', () => {
        expect(conditionFor({ summary: null, periods: [] }, 'dawn')).toBeNull();
    });
});
