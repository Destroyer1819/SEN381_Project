import type { Paginator } from '@/types/civic';
import { Link } from '@inertiajs/react';

export default function Pagination({ paginator }: { paginator: Paginator<unknown> }) {
    if (paginator.last_page <= 1) return null;

    return (
        <nav className="flex items-center justify-between text-sm" aria-label="Pagination">
            {paginator.prev_page_url ? (
                <Link href={paginator.prev_page_url} preserveScroll className="text-primary hover:underline">
                    ← Previous
                </Link>
            ) : (
                <span />
            )}
            <span className="text-muted-foreground">
                Page {paginator.current_page} of {paginator.last_page}
            </span>
            {paginator.next_page_url ? (
                <Link href={paginator.next_page_url} preserveScroll className="text-primary hover:underline">
                    Next →
                </Link>
            ) : (
                <span />
            )}
        </nav>
    );
}
