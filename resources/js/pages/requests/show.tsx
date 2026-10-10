import InputError from '@/components/input-error';
import StatusBadge from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { formatDateTime, statusLabel, transitionLabel, transitionNeedsNote } from '@/lib/workflow';
import { type BreadcrumbItem } from '@/types';
import type { CommentItem, HistoryItem, RequestDetail } from '@/types/civic';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Actions {
    transitions: string[];
    canTake: boolean;
    canOffer: boolean;
    staff: { id: number; name: string }[];
    pendingOfferId: number | null;
}

function Panel({ title, children }: { title?: string; children: React.ReactNode }) {
    return (
        <div className="bg-card space-y-3 rounded-xl border p-5">
            {title && <h2 className="font-semibold">{title}</h2>}
            {children}
        </div>
    );
}

function StatusForm({ request, transitions }: { request: RequestDetail; transitions: string[] }) {
    const form = useForm({ status: transitions[0], version: request.version, note: '' });
    const needsNote = transitionNeedsNote(form.data.status);

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post(`/requests/${request.id}/status`, { preserveScroll: true, onSuccess: () => form.reset('note') });
    }

    return (
        <form onSubmit={submit} aria-label="Update status">
            <Panel title="Update status">
                <div className="grid gap-2">
                    <Label htmlFor="status">Next step</Label>
                    <NativeSelect id="status" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>
                        {transitions.map((t) => (
                            <option key={t} value={t}>
                                {transitionLabel(request.status, t)}
                            </option>
                        ))}
                    </NativeSelect>
                    <InputError role="alert" message={form.errors.status} />
                </div>
                {needsNote && (
                    <div className="grid gap-2">
                        <Label htmlFor="note">Resolution note</Label>
                        <Textarea id="note" rows={3} value={form.data.note} onChange={(e) => form.setData('note', e.target.value)} />
                        <InputError role="alert" message={form.errors.note} />
                    </div>
                )}
                <InputError role="alert" message={(form.errors as Record<string, string>).request} />
                <Button type="submit" disabled={form.processing}>
                    Apply
                </Button>
            </Panel>
        </form>
    );
}

function OfferForm({ request, staff }: { request: RequestDetail; staff: Actions['staff'] }) {
    const form = useForm({ assigned_to: '' });
    if (staff.length === 0) return null;

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post(`/requests/${request.id}/offer`, { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return (
        <form onSubmit={submit} aria-label="Offer to staff" className="grid gap-2">
            <Label htmlFor="assigned_to">Offer to</Label>
            <div className="flex gap-2">
                <NativeSelect id="assigned_to" value={form.data.assigned_to} onChange={(e) => form.setData('assigned_to', e.target.value)}>
                    <option value="">Choose staff member…</option>
                    {staff.map((s) => (
                        <option key={s.id} value={s.id}>
                            {s.name}
                        </option>
                    ))}
                </NativeSelect>
                <Button type="submit" variant="outline" disabled={!form.data.assigned_to || form.processing}>
                    Offer
                </Button>
            </div>
            <InputError role="alert" message={form.errors.assigned_to} />
        </form>
    );
}

export default function Show({
    request,
    history,
    comments,
    actions,
}: {
    request: RequestDetail;
    history: HistoryItem[];
    comments: CommentItem[];
    actions: Actions;
}) {
    const commentForm = useForm({ body: '' });
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Requests', href: '/requests' },
        { title: request.reference, href: `/requests/${request.id}` },
    ];

    const answer = (decision: 'accept' | 'decline') => router.post(`/assignments/${actions.pendingOfferId}/respond`, { decision });

    function addComment(e: FormEvent) {
        e.preventDefault();
        commentForm.post(`/requests/${request.id}/comments`, { preserveScroll: true, onSuccess: () => commentForm.reset() });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={request.reference} />
            <div className="space-y-4 p-4">
                <div className="space-y-1">
                    <h1 className="text-2xl font-semibold tracking-tight">{request.reference}</h1>
                    <StatusBadge status={request.status} overdue={request.overdue} />
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <div className="space-y-4 lg:col-span-2">
                        <Panel>
                            <dl className="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-1 text-sm">
                                <dt className="text-muted-foreground">Category</dt>
                                <dd>{request.category}</dd>
                                <dt className="text-muted-foreground">Location</dt>
                                <dd>
                                    {request.location}, {request.area}
                                </dd>
                                <dt className="text-muted-foreground">Submitted by</dt>
                                <dd>
                                    {request.requestor} on {formatDateTime(request.created_at)}
                                </dd>
                                <dt className="text-muted-foreground">Assigned to</dt>
                                <dd>{request.assignee ?? 'Not yet assigned'}</dd>
                                {request.resolved_at && (
                                    <>
                                        <dt className="text-muted-foreground">Resolved</dt>
                                        <dd>{formatDateTime(request.resolved_at)}</dd>
                                    </>
                                )}
                            </dl>
                            <h2 className="pt-2 font-semibold">Description</h2>
                            <p className="text-sm whitespace-pre-wrap">{request.description}</p>
                        </Panel>

                        <Panel title="Conversation">
                            {comments.length === 0 && <p className="text-muted-foreground text-sm">No comments yet.</p>}
                            <ul className="divide-y">
                                {comments.map((c) => (
                                    <li key={c.id} className="py-3 text-sm">
                                        <strong>{c.by}</strong>{' '}
                                        <span className="text-muted-foreground text-xs">
                                            {c.role} · {formatDateTime(c.at)}
                                        </span>
                                        {c.is_resolution && (
                                            <span className="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                                                Resolution note
                                            </span>
                                        )}
                                        <p className="mt-1 whitespace-pre-wrap">{c.text}</p>
                                    </li>
                                ))}
                            </ul>
                            <form onSubmit={addComment} aria-label="Add comment" className="grid gap-2">
                                <Label htmlFor="comment">Add a comment</Label>
                                <Textarea
                                    id="comment"
                                    rows={3}
                                    value={commentForm.data.body}
                                    onChange={(e) => commentForm.setData('body', e.target.value)}
                                />
                                <InputError role="alert" message={commentForm.errors.body} />
                                <Button
                                    type="submit"
                                    variant="outline"
                                    className="justify-self-start"
                                    disabled={commentForm.processing || !commentForm.data.body.trim()}
                                >
                                    Post comment
                                </Button>
                            </form>
                        </Panel>
                    </div>

                    <aside className="space-y-4">
                        {actions.pendingOfferId && (
                            <Panel title="You were offered this request">
                                <div className="flex gap-2">
                                    <Button onClick={() => answer('accept')}>Accept</Button>
                                    <Button variant="outline" onClick={() => answer('decline')}>
                                        Decline
                                    </Button>
                                </div>
                            </Panel>
                        )}

                        {(actions.canTake || actions.canOffer) && (
                            <Panel title="Assignment">
                                {actions.canTake && (
                                    <Button onClick={() => router.post(`/requests/${request.id}/take`, {}, { preserveScroll: true })}>
                                        Take this request
                                    </Button>
                                )}
                                {actions.canOffer && <OfferForm request={request} staff={actions.staff} />}
                            </Panel>
                        )}

                        {actions.transitions.length > 0 && <StatusForm key={request.version} request={request} transitions={actions.transitions} />}

                        <Panel title="History">
                            <ol className="border-border space-y-3 border-l-2 pl-4 text-sm">
                                {history.map((h) => (
                                    <li key={h.id} className="flex flex-col">
                                        <strong>
                                            {h.old_status
                                                ? `${statusLabel(h.old_status)} → ${statusLabel(h.new_status)}`
                                                : `Submitted (${statusLabel(h.new_status)})`}
                                        </strong>
                                        <span className="text-muted-foreground text-xs">
                                            {h.by} · {formatDateTime(h.at)}
                                        </span>
                                    </li>
                                ))}
                            </ol>
                        </Panel>
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}
