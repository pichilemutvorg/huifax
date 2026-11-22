/** Period of the day each forecast period covers, by starting hour. */
const PERIOD_STARTS = [
    ['dawn', 0],
    ['morning', 6],
    ['afternoon', 12],
    ['night', 18],
];

/**
 * Forecast period for an hour of the day (0–23).
 *
 * @param {number} hour
 * @returns {'dawn' | 'morning' | 'afternoon' | 'night'}
 */
export function periodForHour(hour) {
    let current = PERIOD_STARTS[0][0];
    for (const [period, start] of PERIOD_STARTS) {
        if (hour >= start) {
            current = period;
        }
    }
    return current;
}

/**
 * What to show for a day: the period matching `preferred` if available, else
 * the first period after it, else the last one before it, else the summary.
 *
 * @param {{ summary: { icon: string | null, text: string } | null, periods: Array<{ period: string, icon: string | null, text: string }> }} day
 * @param {string} preferred
 * @returns {{ period: string | null, icon: string | null, text: string } | null}
 */
export function conditionFor(day, preferred) {
    const periods = day.periods ?? [];
    const order = PERIOD_STARTS.map(([period]) => period);
    const preferredIndex = order.indexOf(preferred);

    const byPeriod = new Map(periods.map((p) => [p.period, p]));
    const candidates = [...order.slice(preferredIndex), ...order.slice(0, preferredIndex).reverse()];

    for (const period of candidates) {
        if (byPeriod.has(period)) {
            return byPeriod.get(period);
        }
    }

    return day.summary ? { period: null, ...day.summary } : null;
}
