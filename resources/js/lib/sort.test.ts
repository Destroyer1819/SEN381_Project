import type { RequestSummary } from '@/types/civic';
import { describe, expect, it } from 'vitest';
import { DEFAULT_SORT, parseSort, sortParams, sortRequests, type SortValue } from './sort';

const row = (id: number, over: Partial<RequestSummary> = {}): RequestSummary => ({
    id,
    reference: `CC-2026${String(id).padStart(6, '0')}`,
    status: 'open',
    category: 'Facility fault',
    area: 'Admin block',
    location: 'Room 1',
    description: 'A test request description.',
    requestor: 'Req',
    assignee: null,
    overdue: false,
    created_at: `2026-10-0${id}T08:00:00Z`,
    updated_at: `2026-10-0${id}T09:00:00Z`,
    ...over,
});

const ids = (rows: RequestSummary[]) => rows.map((r) => r.id);

describe('parseSort', () => {
    // TC-U-13 | FR-003 | equivalence: valid, missing and tampered query values
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

describe('sortRequests', () => {
    // TC-U-14 | FR-003 | each sort option orders the visible rows correctly
    it('orders by submitted and updated time', () => {
        const rows = [row(2), row(3), row(1)];
        expect(ids(sortRequests(rows, 'created_at:desc'))).toEqual([3, 2, 1]);
        expect(ids(sortRequests(rows, 'created_at:asc'))).toEqual([1, 2, 3]);
        expect(ids(sortRequests([row(1, { updated_at: '2026-10-09T00:00:00Z' }), row(2), row(3)], 'updated_at:desc'))).toEqual([1, 3, 2]);
    });

    it('orders references by number, not as text', () => {
        const rows = [row(1, { reference: 'CC-2026000010' }), row(2, { reference: 'CC-2026000002' }), row(3, { reference: 'CC-2026000100' })];
        expect(ids(sortRequests(rows, 'reference:asc'))).toEqual([2, 1, 3]);
        expect(ids(sortRequests(rows, 'reference:desc'))).toEqual([3, 1, 2]);
    });

    it('orders status along the workflow and category without regard to case', () => {
        const rows = [
            row(1, { status: 'closed', category: 'it support' }),
            row(2, { status: 'open', category: 'Maintenance' }),
            row(3, { status: 'in_progress', category: 'Facility fault' }),
            row(4, { status: 'resolved', category: 'Lost property' }),
        ];
        expect(ids(sortRequests(rows, 'status:asc'))).toEqual([2, 3, 4, 1]);
        expect(ids(sortRequests(rows, 'status:desc'))).toEqual([1, 4, 3, 2]);
        expect(ids(sortRequests(rows, 'category:asc'))).toEqual([3, 1, 4, 2]);
    });

    // TC-U-14b | negative: does not change the caller's data, and ties and missing categories do not break it
    it('never mutates the input and breaks ties by highest id', () => {
        const rows = [row(1, { category: null }), row(2, { category: null }), row(3)];
        const copy = [...rows];
        const sorted = sortRequests(rows, 'category:asc' as SortValue);
        expect(rows).toEqual(copy);
        expect(ids(sorted)).toEqual([2, 1, 3]);
    });
});
