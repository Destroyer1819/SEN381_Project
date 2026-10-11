import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import StatusBadge from './status-badge';

describe('StatusBadge', () => {
    // TC-U-07 | FR-002 | status is shown as readable text (not by colour alone)
    it.each([
        ['open', 'Open'],
        ['assigned', 'Assigned'],
        ['in_progress', 'In progress'],
        ['resolved', 'Resolved'],
        ['closed', 'Closed'],
    ])('renders %s as "%s"', (status, label) => {
        render(<StatusBadge status={status} />);
        expect(screen.getByText(label)).toHaveAttribute('data-status', status);
    });

    it('shows an Overdue badge only when overdue', () => {
        const { rerender } = render(<StatusBadge status="open" />);
        expect(screen.queryByText('Overdue')).not.toBeInTheDocument();
        rerender(<StatusBadge status="open" overdue />);
        expect(screen.getByText('Overdue')).toBeInTheDocument();
    });
});
