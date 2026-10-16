import { formatDuration } from '@/lib/duration';

interface Props {
    /** Mean hours from submission to resolution. Null or missing when nothing was resolved in the period. */
    hours?: number | null;
}

/** Average resolution time tile for the management dashboard (FR-007). */
export default function AvgResolutionCard({ hours }: Props) {
    const hasValue = typeof hours === 'number' && Number.isFinite(hours) && hours >= 0;

    return (
        <section aria-labelledby="avg-resolution-title" className="bg-card rounded-xl border p-5">
            <h2 id="avg-resolution-title" className="text-muted-foreground text-sm font-medium">
                Average resolution time
            </h2>
            <p className="mt-1 text-3xl leading-tight font-bold" data-testid="avg-resolution">
                {hasValue ? formatDuration(hours) : 'No data'}
            </p>
            <p className="text-muted-foreground mt-1 text-xs">
                {hasValue ? 'Mean time from submission to resolution, for requests resolved in this period.' : 'No resolved requests in this period.'}
            </p>
        </section>
    );
}
