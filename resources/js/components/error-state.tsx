import { Button } from '@/components/ui/button';
import { errorContent } from '@/lib/errors';

interface Props {
    status: number;
    /** When set, the second button closes the message instead of going back in the browser history. */
    onDismiss?: () => void;
}

/** Friendly screen for 403 (no access) and 404 (not found). */
export default function ErrorState({ status, onDismiss }: Props) {
    const { title, message } = errorContent(status);

    return (
        <div className="flex min-h-[60vh] flex-col items-center justify-center gap-4 p-6 text-center">
            <p className="text-muted-foreground text-6xl font-bold tabular-nums" aria-hidden="true">
                {status}
            </p>
            <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
            <p className="text-muted-foreground max-w-md text-sm">{message}</p>
            <div className="flex flex-wrap justify-center gap-2">
                <Button asChild>
                    <a href="/requests">Back to requests</a>
                </Button>
                <Button type="button" variant="outline" onClick={onDismiss ?? (() => window.history.back())}>
                    {onDismiss ? 'Close' : 'Go back'}
                </Button>
            </div>
        </div>
    );
}
