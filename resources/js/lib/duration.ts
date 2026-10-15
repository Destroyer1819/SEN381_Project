/**
 * Turns a number of hours into a short, readable duration for the dashboard.
 * 0.5 -> "30 min", 2.5 -> "2.5 h", 52 -> "2 d 4 h". Invalid or negative input gives an empty string.
 */
export function formatDuration(hours: number): string {
    if (!Number.isFinite(hours) || hours < 0) return '';

    const minutes = Math.round(hours * 60);
    if (minutes < 60) return `${minutes} min`;
    if (hours < 24) return `${Math.round(hours * 10) / 10} h`;

    let days = Math.floor(hours / 24);
    let rest = Math.round(hours - days * 24);
    if (rest === 24) {
        days += 1;
        rest = 0;
    }
    return rest === 0 ? `${days} d` : `${days} d ${rest} h`;
}
