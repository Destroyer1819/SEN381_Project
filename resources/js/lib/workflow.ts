import type { Status } from '@/types/civic';

export const STATUS_LABELS: Record<Status, string> = {
    open: 'Open',
    assigned: 'Assigned',
    in_progress: 'In progress',
    resolved: 'Resolved',
    closed: 'Closed',
};

export const statusLabel = (status: string): string => STATUS_LABELS[status as Status] ?? status;

const ACTION_LABELS: Record<string, string> = {
    'assigned>in_progress': 'Start work',
    'in_progress>resolved': 'Mark as resolved',
    'resolved>closed': 'Close request',
    'resolved>in_progress': 'Re-open (back to in progress)',
};

export const transitionLabel = (from: string, to: string): string => ACTION_LABELS[`${from}>${to}`] ?? statusLabel(to);

// Resolving requires a resolution note (enforced by the server too).
export const transitionNeedsNote = (to: string): boolean => to === 'resolved';

/** Drops empty filter values so URLs stay clean and the server never receives "status=". */
export function cleanFilters<T extends Record<string, unknown>>(filters: T): Partial<T> {
    return Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== '' && v !== null && v !== undefined && v !== false)) as Partial<T>;
}

export function formatDateTime(iso: string | null | undefined): string {
    if (!iso) return '';
    return new Intl.DateTimeFormat('en-ZA', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso));
}
