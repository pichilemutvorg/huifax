/**
 * Calendar date (YYYY-MM-DD) of a moment in the given timezone.
 *
 * @param {Date} moment
 * @param {string | undefined} timeZone
 * @returns {string}
 */
export function dateIn(moment, timeZone) {
    // en-CA formats dates as YYYY-MM-DD.
    return new Intl.DateTimeFormat('en-CA', { timeZone, year: 'numeric', month: '2-digit', day: '2-digit' }).format(
        moment,
    );
}

/**
 * The given date (YYYY-MM-DD) plus a number of days.
 *
 * @param {string} date
 * @param {number} days
 * @returns {string}
 */
export function addDays(date, days) {
    const moment = new Date(`${date}T12:00:00Z`);
    moment.setUTCDate(moment.getUTCDate() + days);

    return moment.toISOString().slice(0, 10);
}

/**
 * Airings that have not started yet, at most `count` of them.
 *
 * @template {{ starts_at: string }} T
 * @param {T[]} airings Chronological list.
 * @param {Date} now
 * @param {number} count
 * @returns {T[]}
 */
export function upcomingAirings(airings, now, count) {
    return airings.filter((airing) => Date.parse(airing.starts_at) >= now.getTime()).slice(0, count);
}

/**
 * Splits a chronological list of airings by the local date they start on.
 *
 * @template {{ starts_at: string }} T
 * @param {T[]} airings
 * @param {string | undefined} timeZone
 * @returns {Array<{ date: string, airings: T[] }>}
 */
export function groupByDate(airings, timeZone) {
    const groups = [];

    for (const airing of airings) {
        const date = dateIn(new Date(airing.starts_at), timeZone);
        const last = groups.at(-1);

        if (last?.date === date) {
            last.airings.push(airing);
        } else {
            groups.push({ date, airings: [airing] });
        }
    }

    return groups;
}

/**
 * "Today", "Tomorrow" or the weekday and day of month ("sábado 29").
 *
 * @param {string} date YYYY-MM-DD.
 * @param {Date} now
 * @param {{ locale?: string, timeZone?: string, today?: string, tomorrow?: string }} options
 * @returns {string}
 */
export function dayLabel(date, now, { locale, timeZone, today, tomorrow }) {
    const current = dateIn(now, timeZone);

    if (date === current && today) {
        return today;
    }

    if (date === addDays(current, 1) && tomorrow) {
        return tomorrow;
    }

    return new Intl.DateTimeFormat(locale, { weekday: 'long', day: 'numeric', timeZone: 'UTC' }).format(
        new Date(`${date}T12:00:00Z`),
    );
}

/**
 * Local start time of an airing, 24-hour clock ("21:30").
 *
 * @param {string} isoMoment
 * @param {string | undefined} locale
 * @param {string | undefined} timeZone
 * @returns {string}
 */
export function formatTime(isoMoment, locale, timeZone) {
    return new Intl.DateTimeFormat(locale, { timeZone, hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).format(
        new Date(isoMoment),
    );
}
