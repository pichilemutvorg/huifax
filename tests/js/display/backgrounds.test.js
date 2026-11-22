import { describe, expect, it } from 'vitest';
import { pickBackground } from '../../../resources/js/display/lib/backgrounds.js';

const rotation = [{ url: '/a.jpg' }, { url: '/b.jpg' }];

describe('pickBackground', () => {
    it('prefers the slide background', () => {
        expect(pickBackground({ background: '/own.jpg' }, rotation, 0)).toBe('/own.jpg');
    });

    it('rotates through the general backgrounds and wraps', () => {
        expect(pickBackground({ background: null }, rotation, 0)).toBe('/a.jpg');
        expect(pickBackground(null, rotation, 1)).toBe('/b.jpg');
        expect(pickBackground(null, rotation, 2)).toBe('/a.jpg');
    });

    it('returns null when there is nothing to show', () => {
        expect(pickBackground(null, [], 3)).toBeNull();
    });
});
