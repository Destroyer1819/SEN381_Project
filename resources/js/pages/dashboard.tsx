import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import AppLayout from '@/layouts/app-layout';
import { cleanFilters, statusLabel } from '@/lib/workflow';
import { type BreadcrumbItem } from '@/types';
import type { Category, RequestFilters } from '@/types/civic';
import { Head, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface Stats {
    total: number;
    by_status: Record<string, number>;
    overdue: number;
    overdue_days: number;
    by_category: { name: string; total: number }[];
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

export default function Dashboard({ filters, categories, stats }: { filters: RequestFilters; categories: Category[]; stats: Stats }) {
    const [values, setValues] = useState({ category_id: String(filters.category_id ?? ''), from: filters.from ?? '', to: filters.to ?? '' });
    const set = (f: keyof typeof values) => (e: { target: { value: string } }) => setValues((v) => ({ ...v, [f]: e.target.value }));

    const apply = (e: FormEvent) => {
        e.preventDefault();
        router.get('/dashboard', cleanFilters(values) as Record<string, string>, { preserveState: true, replace: true });
    };
    const reset = () => {
        setValues({ category_id: '', from: '', to: '' });
        router.get('/dashboard', {}, { replace: true });
    };

    const max = Math.max(1, ...stats.by_category.map((c) => c.total));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="space-y-4 p-4">
                <h1 className="text-2xl font-semibold tracking-tight">Management dashboard</h1>

                <form
                    onSubmit={apply}
                    aria-label="Dashboard filters"
                    className="bg-card grid gap-4 rounded-xl border p-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="d-category">Category</Label>
                        <NativeSelect id="d-category" value={values.category_id} onChange={set('category_id')}>
                            <option value="">All</option>
                            {categories.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </NativeSelect>
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="d-from">From</Label>
                        <Input id="d-from" type="date" value={values.from} onChange={set('from')} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="d-to">To</Label>
                        <Input id="d-to" type="date" value={values.to} onChange={set('to')} />
                    </div>
                    <div className="flex items-end gap-2">
                        <Button type="submit">Apply</Button>
                        <Button type="button" variant="outline" onClick={reset}>
                            Reset
                        </Button>
                    </div>
                </form>

                <div className="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
                    <Tile label="Total" value={stats.total} testId="total" />
                    {Object.entries(stats.by_status).map(([status, n]) => (
                        <Tile key={status} label={statusLabel(status)} value={n} testId={`count-${status}`} />
                    ))}
                    <Tile label={`Overdue (> ${stats.overdue_days} days)`} value={stats.overdue} testId="overdue" alert />
                </div>

                <div className="bg-card space-y-3 rounded-xl border p-5">
                    <h2 className="font-semibold">Requests by category</h2>
                    {stats.by_category.length === 0 && <p className="text-muted-foreground text-sm">No requests in this period.</p>}
                    <ul className="space-y-2">
                        {stats.by_category.map((c) => (
                            <li key={c.name} className="grid grid-cols-[9rem_1fr_2.5rem] items-center gap-3 text-sm">
                                <span>{c.name}</span>
                                <span className="bg-muted h-2.5 overflow-hidden rounded-full">
                                    <span className="bg-primary block h-full rounded-full" style={{ width: `${(c.total / max) * 100}%` }} />
                                </span>
                                <span className="text-right tabular-nums">{c.total}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </AppLayout>
    );
}

function Tile({ label, value, testId, alert = false }: { label: string; value: number; testId: string; alert?: boolean }) {
    return (
        <div className="bg-card flex flex-col rounded-xl border p-4">
            <span className={`text-3xl leading-tight font-bold ${alert ? 'text-red-600 dark:text-red-400' : ''}`} data-testid={testId}>
                {value}
            </span>
            <span className="text-muted-foreground text-xs">{label}</span>
        </div>
    );
}
