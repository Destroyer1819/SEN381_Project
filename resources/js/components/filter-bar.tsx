import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import { cleanFilters, statusLabel } from '@/lib/workflow';
import type { Category, RequestFilters } from '@/types/civic';
import { FormEvent, useState } from 'react';

interface Props {
    filters?: RequestFilters;
    categories: Category[];
    statuses: string[];
    onApply: (filters: Record<string, unknown>) => void;
    showMine?: boolean;
}

/** Search + filters for the request list (FR-003). Calls onApply with only the filled-in values. */
export default function FilterBar({ filters = {}, categories, statuses, onApply, showMine = false }: Props) {
    const [values, setValues] = useState({
        q: filters.q ?? '',
        status: filters.status ?? '',
        category_id: String(filters.category_id ?? ''),
        from: filters.from ?? '',
        to: filters.to ?? '',
        mine: Boolean(filters.mine),
    });
    const set = (field: keyof typeof values) => (e: { target: { value: string; checked?: boolean; type?: string } }) =>
        setValues((v) => ({ ...v, [field]: e.target.type === 'checkbox' ? Boolean(e.target.checked) : e.target.value }));

    function submit(e: FormEvent) {
        e.preventDefault();
        onApply(cleanFilters(values));
    }

    function reset() {
        setValues({ q: '', status: '', category_id: '', from: '', to: '', mine: false });
        onApply({});
    }

    return (
        <form onSubmit={submit} aria-label="Filter requests" className="bg-card grid gap-4 rounded-xl border p-4 sm:grid-cols-2 lg:grid-cols-6">
            <div className="grid gap-2 lg:col-span-2">
                <Label htmlFor="f-q">Search</Label>
                <Input id="f-q" type="search" value={values.q} onChange={set('q')} placeholder="Reference, location or text" />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="f-status">Status</Label>
                <NativeSelect id="f-status" value={values.status} onChange={set('status')}>
                    <option value="">All</option>
                    {statuses.map((s) => (
                        <option key={s} value={s}>
                            {statusLabel(s)}
                        </option>
                    ))}
                </NativeSelect>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="f-category">Category</Label>
                <NativeSelect id="f-category" value={values.category_id} onChange={set('category_id')}>
                    <option value="">All</option>
                    {categories.map((c) => (
                        <option key={c.id} value={c.id}>
                            {c.name}
                        </option>
                    ))}
                </NativeSelect>
            </div>
            <div className="grid gap-2">
                <Label htmlFor="f-from">From</Label>
                <Input id="f-from" type="date" value={values.from} onChange={set('from')} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="f-to">To</Label>
                <Input id="f-to" type="date" value={values.to} onChange={set('to')} />
            </div>
            {showMine && (
                <label className="flex items-center gap-2 text-sm font-medium">
                    <input type="checkbox" checked={values.mine} onChange={set('mine')} className="size-4" />
                    Assigned to me
                </label>
            )}
            <div className="flex items-end gap-2 lg:col-span-2">
                <Button type="submit">Apply</Button>
                <Button type="button" variant="outline" onClick={reset}>
                    Reset
                </Button>
            </div>
        </form>
    );
}
