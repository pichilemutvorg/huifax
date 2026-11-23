import { describe, expect, it } from 'vitest';
import { pickNextSlide, pickPreviousSlide } from '../../../resources/js/display/lib/rotation.js';

const slides = (...ids) => ids.map((id) => ({ id }));

describe('pickNextSlide', () => {
    it('returns null when there is nothing to show', () => {
        expect(pickNextSlide(slides('a'), [], 'a')).toBeNull();
    });

    it('starts from the first slide', () => {
        expect(pickNextSlide([], slides('a', 'b'), null).id).toBe('a');
    });

    it('advances and wraps around', () => {
        const list = slides('a', 'b', 'c');
        expect(pickNextSlide(list, list, 'a').id).toBe('b');
        expect(pickNextSlide(list, list, 'c').id).toBe('a');
    });

    it('keeps showing the only slide', () => {
        const list = slides('a');
        expect(pickNextSlide(list, list, 'a').id).toBe('a');
    });

    it('continues after the current slide when content changes around it', () => {
        expect(pickNextSlide(slides('a', 'b', 'c'), slides('x', 'b', 'y', 'c'), 'b').id).toBe('y');
    });

    it('continues from the same position when the current slide was removed', () => {
        expect(pickNextSlide(slides('a', 'b', 'c'), slides('a', 'c'), 'b').id).toBe('c');
    });

    it('wraps when the removed slide was the last one', () => {
        expect(pickNextSlide(slides('a', 'b', 'c'), slides('a', 'b'), 'c').id).toBe('a');
    });

    it('starts over when the current slide is unknown', () => {
        expect(pickNextSlide(slides('a'), slides('x', 'y'), 'zzz').id).toBe('x');
    });
});

describe('pickPreviousSlide', () => {
    const slides = [{ id: 'a' }, { id: 'b' }, { id: 'c' }];

    it('returns the slide before the current one, wrapping around', () => {
        expect(pickPreviousSlide(slides, 'b').id).toBe('a');
        expect(pickPreviousSlide(slides, 'a').id).toBe('c');
    });

    it('starts from the last slide when the current one is unknown', () => {
        expect(pickPreviousSlide(slides, null).id).toBe('c');
        expect(pickPreviousSlide(slides, 'gone').id).toBe('c');
    });

    it('returns null when there is nothing to show', () => {
        expect(pickPreviousSlide([], 'a')).toBeNull();
    });
});
