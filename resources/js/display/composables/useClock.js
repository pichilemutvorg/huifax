import { computed, onBeforeUnmount, onMounted, ref, toValue } from 'vue';

/**
 * Current date and time, formatted for the instance locale and timezone
 * (never the machine's). Ticks on each second boundary.
 *
 * @param {import('vue').MaybeRefOrGetter<string>} locale
 * @param {import('vue').MaybeRefOrGetter<string>} timeZone
 */
export function useClock(locale, timeZone) {
    const now = ref(new Date());
    let timer = null;

    function tick() {
        now.value = new Date();
        timer = setTimeout(tick, 1000 - (Date.now() % 1000));
    }

    onMounted(tick);
    onBeforeUnmount(() => clearTimeout(timer));

    const formatters = computed(() => {
        const options = { timeZone: toValue(timeZone) || undefined };
        const lang = toValue(locale) || undefined;

        return {
            time: new Intl.DateTimeFormat(lang, { ...options, hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }),
            date: new Intl.DateTimeFormat(lang, {
                ...options,
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }),
        };
    });

    return {
        now,
        time: computed(() => formatters.value.time.format(now.value)),
        date: computed(() => formatters.value.date.format(now.value)),
    };
}
