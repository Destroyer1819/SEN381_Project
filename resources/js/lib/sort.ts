import type { RequestSummary, Status } from '@/types/civic';

export type SortField = 'created_at' | 'updated_at' | 'reference' | 'status' | 'category';
export type SortDirection = 'asc' | 'desc';

/**
 * Sort choices offered on the request list.
 * Query contract with the server: `?sort=<field>&direction=<asc|desc>`.
 * The default (newest first) is sent as no parameters at all, which matches the server's current `latest()` order.
 */
export const SORT_OPTIONS = [
    { value: 'created_at:desc', label: 'Newest first' },
    { value: 'created_at:asc', label: 'Oldest first' },
    { value: 'updated_at:desc', label: 'Recently updated' },
    { value: 'reference:asc', label: 'Reference (low to high)' },
    { value: 'reference:desc', label: 'Reference (high to low)' },
    { value: 'status:asc', label: 'Status (open first)' },
    { value: 'status:desc', label: 'Status (closed first)' },
    { value: 'category:asc', label: 'Category (A to Z)' },
] as const;

export type SortValue = (typeof SORT_OPTIONS)[number]['value'];

export const DEFAULT_SORT: SortValue = 'created_at:desc';

const VALID_VALUES = new Set<string>(SORT_OPTIONS.map((option) => option.value));

/** Follows the workflow, not the alphabet: open, assigned, in progress, resolved, closed. */
const STATUS_ORDER: Record<Status, number> = { open: 0, assigned: 1, in_progress: 2, resolved: 3, closed: 4 };

/** Reads `sort` and `direction` from a URL or query string. Anything missing or unknown falls back to the default. */
export function parseSort(urlOrQuery: string): SortValue {
    const queryStart = urlOrQuery.indexOf('?');
    const query = queryStart === -1 ? urlOrQuery : urlOrQuery.slice(queryStart + 1).split('#')[0];
    const params = new URLSearchParams(query);
    const value = `${params.get('sort')}:${params.get('direction')}`;
    return VALID_VALUES.has(value) ? (value as SortValue) : DEFAULT_SORT;
}

/** Query parameters for a sort choice. The default sends nothing so URLs stay clean. */
export function sortParams(value: SortValue): { sort?: SortField; direction?: SortDirection } {
    if (value === DEFAULT_SORT) return {};
    const [sort, direction] = value.split(':') as [SortField, SortDirection];
    return { sort, direction };
}

const referenceNumber = (r: RequestSummary): number => Number(r.reference.replace(/\D/g, '')) || 0;

const SORT_KEYS: Record<SortField, (r: RequestSummary) => number | string> = {
    created_at: (r) => Date.parse(r.created_at) || 0,
    updated_at: (r) => Date.parse(r.updated_at) || 0,
    reference: referenceNumber,
    status: (r) => STATUS_ORDER[r.status] ?? Number.MAX_SAFE_INTEGER,
    category: (r) => (r.category ?? '').toLowerCase(),
};

/**
 * Orders the rows already on screen. The server is the source of truth across pages;
 * this keeps the visible page in the chosen order. Returns a new array and never mutates the input.
 */
export function sortRequests(rows: RequestSummary[], value: SortValue): RequestSummary[] {
    const [field, direction] = value.split(':') as [SortField, SortDirection];
    const sign = direction === 'asc' ? 1 : -1;
    const key = SORT_KEYS[field];

    return [...rows].sort((a, b) => {
        const ka = key(a);
        const kb = key(b);
        const compared = typeof ka === 'string' || typeof kb === 'string' ? String(ka).localeCompare(String(kb)) : ka - kb;
        // Ties keep a stable, predictable order: highest id first.
        return compared !== 0 ? sign * compared : b.id - a.id;
    });
}
