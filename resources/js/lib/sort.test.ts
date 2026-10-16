import { describe, expect, it } from 'vitest';
import { DEFAULT_SORT, parseSort, sortParams } from './sort';

// Ordering itself is done and tested on the server (tests/Feature/RequestSortTest.php)
describe('parseSort', () => {
    // TC-U-13 | FR-005, FR-013 | equivalence: valid, missing and tampered query values
    it('reads a valid sort from a URL and falls back to newest first otherwise', () => {
        expect(parseSort('/requests?status=open&sort=reference&direction=asc')).toBe('reference:asc');
        expect(parseSort('sort=category&direction=asc')).toBe('category:asc');
        expect(parseSort('/requests')).toBe(DEFAULT_SORT);
        expect(parseSort('/requests?sort=reference')).toBe(DEFAULT_SORT);
        expect(parseSort('/requests?sort=password&direction=asc')).toBe(DEFAULT_SORT);
        expect(parseSort('/requests?sort=reference&direction=sideways#top')).toBe(DEFAULT_SORT);
    });

    it('sends no query parameters for the default sort', () => {
        expect(sortParams(DEFAULT_SORT)).toEqual({});
        expect(sortParams('reference:desc')).toEqual({ sort: 'reference', direction: 'desc' });
    });
});
