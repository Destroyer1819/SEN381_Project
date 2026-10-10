import { cn } from '@/lib/utils';
import { statusLabel } from '@/lib/workflow';

const COLOURS: Record<string, string> = {
    open: 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
    assigned: 'bg-blue-100 text-blue-900 dark:bg-blue-950 dark:text-blue-200',
    in_progress: 'bg-violet-100 text-violet-900 dark:bg-violet-950 dark:text-violet-200',
    resolved: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
    closed: 'bg-neutral-200 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200',
};

export default function StatusBadge({ status, overdue = false }: { status: string; overdue?: boolean }) {
    return (
        <span className="inline-flex flex-wrap items-center gap-1">
            <span data-status={status} className={cn('inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold', COLOURS[status])}>
                {statusLabel(status)}
            </span>
            {overdue && (
                <span className="inline-block rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-900 dark:bg-red-950 dark:text-red-200">
                    Overdue
                </span>
            )}
        </span>
    );
}
