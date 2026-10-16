export type SortField = 'created_at' | 'updated_at' | 'reference' | 'status' | 'category';
export type SortDirection = 'asc' | 'desc';

/**
 * Sort choices offered on the request list.
 * Query contract with the server: `?sort=<field>&direction=<asc|desc>`.
 * The server does the sorting across all pages (ServiceRequestController::applySort), with status in workflow order
 * and ties broken by the newest id. The default (newest first) is sent as no parameters at all.
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
