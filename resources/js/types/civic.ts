export type Status = 'open' | 'assigned' | 'in_progress' | 'resolved' | 'closed';

export interface Category {
    id: number;
    name: string;
}

export interface RequestSummary {
    id: number;
    reference: string;
    status: Status;
    category: string | null;
    area: string;
    location: string;
    description: string;
    requestor: string | null;
    assignee: string | null;
    overdue: boolean;
    created_at: string;
    updated_at: string;
}

export interface RequestDetail extends RequestSummary {
    version: number;
    resolved_at: string | null;
}

export interface Paginator<T> {
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

export interface RequestFilters {
    q?: string;
    status?: string;
    category_id?: string | number;
    from?: string;
    to?: string;
    mine?: boolean | string | number;
    active?: boolean | string | number;
    overdue?: boolean | string | number;
}

export interface HistoryItem {
    id: number;
    old_status: Status | null;
    new_status: Status;
    by: string | null;
    at: string;
}

/** One self-assignment, offer, acceptance or decline (FR-016). Only staff and management receive these. */
export interface AssignmentItem {
    id: number;
    state: 'offered' | 'accepted' | 'declined' | 'self_assigned';
    to: string | null;
    by: string | null;
    at: string;
    responded_at: string | null;
}

export interface CommentItem {
    id: number;
    text: string;
    is_resolution: boolean;
    by: string | null;
    role: string | null;
    at: string;
}
