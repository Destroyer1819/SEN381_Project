import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import { LIMITS, validateRequestForm, type RequestFormErrors } from '@/lib/validation';
import type { Category } from '@/types/civic';
import { FormEvent, useState } from 'react';

export interface RequestFormValues {
    category_id: number;
    location: string;
    area: string;
    description: string;
}

interface Props {
    categories: Category[];
    onSubmit: (values: RequestFormValues) => void;
    processing?: boolean;
    serverErrors?: RequestFormErrors;
}

/**
 * Controlled "submit a request" form (FR-001).
 * Validates on the client first, then hands clean values to onSubmit.
 * serverErrors lets the page show messages that only the server can produce.
 */
export default function RequestForm({ categories, onSubmit, processing = false, serverErrors = {} }: Props) {
    const [values, setValues] = useState({ category_id: '', location: '', area: '', description: '' });
    const [clientErrors, setClientErrors] = useState<RequestFormErrors>({});

    const errors: RequestFormErrors = { ...serverErrors, ...clientErrors };
    const set = (field: keyof typeof values) => (e: { target: { value: string } }) => setValues((v) => ({ ...v, [field]: e.target.value }));

    function submit(e: FormEvent) {
        e.preventDefault();
        const found = validateRequestForm(values);
        setClientErrors(found);
        if (Object.keys(found).length === 0) {
            onSubmit({
                category_id: Number(values.category_id),
                location: values.location.trim(),
                area: values.area.trim(),
                description: values.description.trim(),
            });
        }
    }

    const field = (name: keyof RequestFormErrors) => ({
        'aria-invalid': errors[name] ? (true as const) : undefined,
        'aria-describedby': errors[name] ? `${name}-error` : undefined,
    });
    const error = (name: keyof RequestFormErrors) => <InputError id={`${name}-error`} role="alert" message={errors[name]} />;

    return (
        <form onSubmit={submit} noValidate className="grid gap-5">
            <div className="grid gap-2">
                <Label htmlFor="category_id">Category</Label>
                <NativeSelect id="category_id" value={values.category_id} onChange={set('category_id')} {...field('category_id')}>
                    <option value="">Select a category…</option>
                    {categories.map((c) => (
                        <option key={c.id} value={c.id}>
                            {c.name}
                        </option>
                    ))}
                </NativeSelect>
                {error('category_id')}
            </div>

            <div className="grid gap-5 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="location">Location</Label>
                    <Input id="location" value={values.location} onChange={set('location')} placeholder="e.g. Main hall" {...field('location')} />
                    {error('location')}
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="area">Area</Label>
                    <Input id="area" value={values.area} onChange={set('area')} placeholder="e.g. North wing" {...field('area')} />
                    {error('area')}
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Description</Label>
                <Textarea id="description" rows={5} value={values.description} onChange={set('description')} {...field('description')} />
                <p className="text-muted-foreground text-xs">
                    {values.description.trim().length} / {LIMITS.descriptionMax} characters (minimum {LIMITS.descriptionMin})
                </p>
                {error('description')}
            </div>

            <Button type="submit" disabled={processing} className="w-full sm:w-auto sm:justify-self-start">
                {processing ? 'Submitting…' : 'Submit request'}
            </Button>
        </form>
    );
}
