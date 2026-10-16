import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import ErrorState from './error-state';

describe('ErrorState', () => {
    // TC-U-19 | FR-010, FR-011 | negative: a forbidden page explains the problem and offers a way out
    it('shows the 403 wording with a link back to the requests', () => {
        render(<ErrorState status={403} />);
        expect(screen.getByRole('heading', { name: 'You do not have access to this page' })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Back to requests' })).toHaveAttribute('href', '/requests');
    });

    it('shows the 404 wording and goes back in the browser history', async () => {
        const back = vi.spyOn(window.history, 'back').mockImplementation(() => {});
        const user = userEvent.setup();
        render(<ErrorState status={404} />);

        expect(screen.getByRole('heading', { name: 'Page not found' })).toBeInTheDocument();
        await user.click(screen.getByRole('button', { name: 'Go back' }));
        expect(back).toHaveBeenCalledOnce();
        back.mockRestore();
    });

    it('offers Close instead of Go back when it is shown over the current page', async () => {
        const onDismiss = vi.fn();
        const user = userEvent.setup();
        render(<ErrorState status={403} onDismiss={onDismiss} />);

        await user.click(screen.getByRole('button', { name: 'Close' }));
        expect(onDismiss).toHaveBeenCalledOnce();
        expect(screen.queryByRole('button', { name: 'Go back' })).not.toBeInTheDocument();
    });

    it('falls back to generic wording for other status codes', () => {
        render(<ErrorState status={500} />);
        expect(screen.getByRole('heading', { name: 'Something went wrong' })).toBeInTheDocument();
    });
});
