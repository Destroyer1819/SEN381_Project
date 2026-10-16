import ErrorState from '@/components/error-state';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

/** Render from the server with Inertia::render('errors/404') when a page does not exist. */
export default function NotFound() {
    return (
        <AppLayout>
            <Head title="Page not found" />
            <ErrorState status={404} />
        </AppLayout>
    );
}
