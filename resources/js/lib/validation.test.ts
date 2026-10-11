import { describe, expect, it } from 'vitest';
import { LIMITS, validateRequestForm } from './validation';

const valid = { category_id: 1, location: 'Main hall', area: 'North wing', description: 'The ceiling light flickers.' };

describe('validateRequestForm', () => {
    // TC-U-04 | FR-001 | a complete, valid form produces no errors
    it('accepts a valid form', () => {
        expect(validateRequestForm(valid)).toEqual({});
    });

    // TC-BB-09 | FR-001 | boundary value analysis on description length (mirrors server TC-BB-01)
    it.each([
        [LIMITS.descriptionMin - 1, true],
        [LIMITS.descriptionMin, false],
        [LIMITS.descriptionMax, false],
        [LIMITS.descriptionMax + 1, true],
    ])('description of %i characters -> error: %s', (length, hasError) => {
        const errors = validateRequestForm({ ...valid, description: 'a'.repeat(length) });
        expect(Boolean(errors.description)).toBe(hasError);
    });

    it('treats a whitespace-only description as empty', () => {
        expect(validateRequestForm({ ...valid, description: '          ' }).description).toBe('Describe the problem.');
    });

    // TC-BB-10 | FR-001 | equivalence partitions: missing / valid / too long for location and area
    it.each(['location', 'area'] as const)('%s: empty and 256-char values are rejected, 255 accepted', (field) => {
        expect(validateRequestForm({ ...valid, [field]: '' })[field]).toBeTruthy();
        expect(validateRequestForm({ ...valid, [field]: 'x'.repeat(LIMITS.textMax) })[field]).toBeUndefined();
        expect(validateRequestForm({ ...valid, [field]: 'x'.repeat(LIMITS.textMax + 1) })[field]).toBeTruthy();
    });

    it('requires a category', () => {
        expect(validateRequestForm({ ...valid, category_id: '' }).category_id).toBe('Choose a category.');
    });
});
