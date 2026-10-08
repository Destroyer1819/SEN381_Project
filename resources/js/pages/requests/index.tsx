import FilterBar from '@/components/filter-bar';
import Pagination from '@/components/pagination';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime } from '@/lib/workflow';
import { type BreadcrumbItem, type SharedData } from '@/types';
import type { Category, Paginator, RequestFilters, RequestSummary } from '@/types/civic';
import { Head, Link, router, usePage } from '@inertiajs/react';

interface Props {
    requests: Paginator<RequestSummary>;
    filters: RequestFilters;
    categories: Category[];
    statuses: string[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Requests', href: '/requests' }];

export default function Index({ requests, filters, categories, statuses }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isRequestor = auth.user.role === 'requestor';

    const apply = (cleaned: Record<string, unknown>) =>
        router.get('/requests', cleaned as Record<string, string>, { preserveState: true, replace: true });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isRequestor ? 'My requests' : 'Requests'} />
            <div className="space-y-4 p-4">
                <div className="flex items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold tracking-tight">{isRequestor ? 'My requests' : 'All requests'}</h1>
                    <Button asChild>
                        <Link href="/requests/create">New request</Link>
                    </Button>
                </div>

                <FilterBar filters={filters} categories={categories} statuses={statuses} onApply={apply} showMine={!isRequestor} />

                <p className="text-muted-foreground text-sm" aria-live="polite">
                    {requests.total} request{requests.total === 1 ? '' : 's'}
                </p>

                {requests.data.length === 0 ? (
                    <div className="text-muted-foreground rounded-xl border border-dashed p-10 text-center">
                        <p>No requests match.</p>
                        {isRequestor && (
                            <Link href="/requests/create" className="text-primary hover:underline">
                                Submit your first request
                            </Link>
                        )}
                    </div>
                ) : (
                    <div className="bg-card overflow-x-auto rounded-xl border">
                        <table className="w-full text-sm">
                            <thead className="text-muted-foreground text-left text-xs tracking-wide uppercase">
                                <tr className="border-b">
                                    <th className="px-4 py-3">Reference</th>
                                    <th className="px-4 py-3">Category</th>
                                    <th className="px-4 py-3">Where</th>
                                    <th className="px-4 py-3">Status</th>
                                    {!isRequestor && <th className="px-4 py-3">Assigned to</th>}
                                    <th className="px-4 py-3">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                {requests.data.map((r) => (
                                    <tr key={r.id} className="border-b align-top last:border-0">
                                        <td className="px-4 py-3">
                                            <Link href={`/requests/${r.id}`} className="text-primary font-medium hover:underline">
                                                {r.reference}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">{r.category}</td>
                                        <td className="px-4 py-3">
                                            {r.location}
                                            <br />
                                            <span className="text-muted-foreground text-xs">{r.area}</span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge status={r.status} overdue={r.overdue} />
                                        </td>
                                        {!isRequestor && <td className="px-4 py-3">{r.assignee ?? 'Unassigned'}</td>}
                                        <td className="px-4 py-3">{formatDateTime(r.created_at)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
                <Pagination paginator={requests} />
            </div>
        </AppLayout>
    );
}
