import RequestForm, { type RequestFormValues } from '@/components/request-form';
import AppLayout from '@/layouts/app-layout';
import { type RequestFormErrors } from '@/lib/validation';
import { type BreadcrumbItem } from '@/types';
import type { Category } from '@/types/civic';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Requests', href: '/requests' },
    { title: 'New request', href: '/requests/create' },
];

export default function Create({ categories }: { categories: Category[] }) {
    const [processing, setProcessing] = useState(false);
    const [serverErrors, setServerErrors] = useState<RequestFormErrors>({});

    function submit(values: RequestFormValues) {
        router.post(
            '/requests',
            { ...values },
            {
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onError: (errors) => setServerErrors(errors as RequestFormErrors),
            },
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="New request" />
            <div className="mx-auto w-full max-w-2xl space-y-4 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Submit a request</h1>
                    <p className="text-muted-foreground text-sm">
                        Tell us what needs attention. You will get a reference number and can follow progress from your list.
                    </p>
                </div>
                <div className="bg-card rounded-xl border p-5">
                    <RequestForm categories={categories} onSubmit={submit} processing={processing} serverErrors={serverErrors} />
                </div>
            </div>
        </AppLayout>
    );
}
