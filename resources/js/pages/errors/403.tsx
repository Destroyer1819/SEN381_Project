import ErrorState from '@/components/error-state';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

/** Render from the server with Inertia::render('errors/403') when a page is not allowed for the signed-in role. */
export default function Forbidden() {
    return (
        <AppLayout>
            <Head title="Access denied" />
            <ErrorState status={403} />
        </AppLayout>
    );
}
