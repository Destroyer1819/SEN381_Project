import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export default function FlashMessage() {
    const { flash } = usePage<SharedData>().props;
    if (!flash?.success) return null;

    return (
        <div
            role="status"
            className="mx-4 mt-4 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-100"
        >
            {flash.success}
        </div>
    );
}
