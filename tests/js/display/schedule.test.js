import { describe, expect, it } from 'vitest';
import {
    addDays,
    dateIn,
    dayLabel,
    formatTime,
    groupByDate,
    upcomingAirings,
} from '../../../resources/js/display/lib/schedule.js';

const TZ = 'America/Santiago';
const airing = (startsAt, program = 'Show') => ({ starts_at: startsAt, program });

describe('dateIn', () => {
    it('uses the instance timezone, not UTC', () => {
        // 02:00 UTC on the 23rd is still the 22nd in Santiago (UTC-3 in summer).
        expect(dateIn(new Date('2026-11-23T02:00:00Z'), TZ)).toBe('2026-11-22');
    });
});

describe('addDays', () => {
    it('crosses month and year boundaries', () => {
        expect(addDays('2026-11-30', 1)).toBe('2026-12-01');
        expect(addDays('2026-12-31', 1)).toBe('2027-01-01');
    });
});

describe('upcomingAirings', () => {
    it('drops airings that already started and keeps at most `count`', () => {
        const list = [
            airing('2026-11-22T20:00:00-03:00', 'A'),
            airing('2026-11-22T21:00:00-03:00', 'B'),
            airing('2026-11-22T22:00:00-03:00', 'C'),
            airing('2026-11-22T23:00:00-03:00', 'D'),
        ];
        const now = new Date('2026-11-22T20:30:00-03:00');

        expect(upcomingAirings(list, now, 2).map((a) => a.program)).toEqual(['B', 'C']);
    });
});

describe('groupByDate', () => {
    it('groups consecutive airings by local start date', () => {
        const groups = groupByDate(
            [
                airing('2026-11-22T22:00:00-03:00', 'A'),
                airing('2026-11-22T23:30:00-03:00', 'B'),
                airing('2026-11-23T09:00:00-03:00', 'C'),
            ],
            TZ,
        );

        expect(groups.map((g) => [g.date, g.airings.map((a) => a.program)])).toEqual([
            ['2026-11-22', ['A', 'B']],
            ['2026-11-23', ['C']],
        ]);
    });
});

describe('dayLabel', () => {
    const now = new Date('2026-11-22T23:00:00-03:00');
    const options = { locale: 'es-CL', timeZone: TZ, today: 'Hoy', tomorrow: 'Mañana' };

    it('names today and tomorrow', () => {
        expect(dayLabel('2026-11-22', now, options)).toBe('Hoy');
        expect(dayLabel('2026-11-23', now, options)).toBe('Mañana');
    });

    it('falls back to the weekday and day of month', () => {
        expect(dayLabel('2026-11-25', now, options)).toBe('miércoles 25');
    });
});

describe('formatTime', () => {
    it('formats in the instance timezone with a 24-hour clock', () => {
        expect(formatTime('2026-11-23T00:30:00Z', 'es-CL', TZ)).toBe('21:30');
    });
});
