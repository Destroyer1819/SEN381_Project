import { describe, expect, it } from 'vitest';
import { formatDuration } from './duration';

describe('formatDuration', () => {
    // TC-U-15 | FR-007 | boundary values around the minute, hour and day switch-overs
    it.each([
        [0, '0 min'],
        [0.5, '30 min'],
        [1, '1 h'],
        [2.5, '2.5 h'],
        [23, '23 h'],
        [24, '1 d'],
        [52, '2 d 4 h'],
        [71.6, '3 d'],
    ])('formats %s hours as "%s"', (hours, expected) => {
        expect(formatDuration(hours)).toBe(expected);
    });

    it('returns an empty string for invalid input', () => {
        expect(formatDuration(-1)).toBe('');
        expect(formatDuration(Number.NaN)).toBe('');
        expect(formatDuration(Number.POSITIVE_INFINITY)).toBe('');
    });
});
