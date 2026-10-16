import ErrorState from '@/components/error-state';
import { HANDLED_ERROR_STATUSES } from '@/lib/errors';
import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * When the server answers a page visit with a plain 403 or 404 (no Inertia error page configured),
 * Inertia would show the raw HTML in a modal. This shows our own error screen instead.
 */
export default function HttpErrorOverlay() {
    const [status, setStatus] = useState<number | null>(null);

    useEffect(() => {
        const stopException = router.on('httpException', (event) => {
            const code = event.detail.response.status;
            if (!(HANDLED_ERROR_STATUSES as readonly number[]).includes(code)) return;
            event.preventDefault();
            setStatus(code);
        });
        const stopNavigate = router.on('navigate', () => setStatus(null));

        return () => {
            stopException();
            stopNavigate();
        };
    }, []);

    if (status === null) return null;

    return (
        <div role="alert" className="bg-background fixed inset-0 z-50 overflow-auto">
            <ErrorState status={status} onDismiss={() => setStatus(null)} />
        </div>
    );
}
