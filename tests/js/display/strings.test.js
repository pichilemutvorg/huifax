import { describe, expect, it } from 'vitest';
import { translate } from '../../../resources/js/display/lib/strings.js';

describe('translate', () => {
    it('replaces placeholders', () => {
        expect(translate(':current de :total', { current: 1, total: 3 })).toBe('1 de 3');
    });

    it('leaves unknown placeholders untouched', () => {
        expect(translate('Hola :name')).toBe('Hola :name');
    });

    it('returns an empty string for missing templates', () => {
        expect(translate(undefined)).toBe('');
    });
});
