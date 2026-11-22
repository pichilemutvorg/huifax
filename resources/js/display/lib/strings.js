/**
 * Replaces Laravel-style `:placeholder` tokens in a translated string.
 *
 * @param {string | undefined} template
 * @param {Record<string, string | number>} [replacements]
 */
export function translate(template, replacements = {}) {
    if (typeof template !== 'string') {
        return '';
    }

    return template.replace(/:([a-z_]+)/gi, (match, key) => (key in replacements ? String(replacements[key]) : match));
}
