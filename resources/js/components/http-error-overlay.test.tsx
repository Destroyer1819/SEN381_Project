import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import HttpErrorOverlay from './http-error-overlay';

type Listener = (event: { detail: { response: { status: number } }; preventDefault: () => void }) => void;
const listeners: Record<string, Listener> = {};

vi.mock('@inertiajs/react', () => ({
    router: {
        on: (name: string, callback: Listener) => {
            listeners[name] = callback;
            return () => delete listeners[name];
        },
    },
}));

function fire(status: number) {
    const preventDefault = vi.fn();
    act(() => listeners.httpException({ detail: { response: { status } }, preventDefault }));
    return preventDefault;
}

describe('HttpErrorOverlay', () => {
    beforeEach(() => {
        for (const key of Object.keys(listeners)) delete listeners[key];
    });

    // TC-U-20 | FR-008 | negative: a 403 from the server is shown as our own screen, not raw HTML
    it('shows the 403 screen and stops Inertia from showing its raw HTML modal', () => {
        render(<HttpErrorOverlay />);
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();

        const preventDefault = fire(403);

        expect(preventDefault).toHaveBeenCalledOnce();
        expect(screen.getByRole('heading', { name: 'You do not have access to this page' })).toBeInTheDocument();
    });

    it('shows the 404 screen and can be closed', async () => {
        const user = userEvent.setup();
        render(<HttpErrorOverlay />);
        fire(404);

        expect(screen.getByRole('heading', { name: 'Page not found' })).toBeInTheDocument();
        await user.click(screen.getByRole('button', { name: 'Close' }));
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
    });

    it('ignores other errors and clears itself when the user navigates', () => {
        render(<HttpErrorOverlay />);

        const preventDefault = fire(500);
        expect(preventDefault).not.toHaveBeenCalled();
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();

        fire(404);
        expect(screen.getByRole('alert')).toBeInTheDocument();
        act(() => listeners.navigate({ detail: { response: { status: 200 } }, preventDefault: vi.fn() }));
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
    });
});
