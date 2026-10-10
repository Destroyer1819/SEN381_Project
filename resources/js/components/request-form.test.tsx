import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import RequestForm from './request-form';

const categories = [
    { id: 1, name: 'Facility fault' },
    { id: 2, name: 'IT support' },
];

async function fillValid(user: ReturnType<typeof userEvent.setup>) {
    await user.selectOptions(screen.getByLabelText('Category'), '2');
    await user.type(screen.getByLabelText('Location'), '  Room 204 ');
    await user.type(screen.getByLabelText('Area'), 'Admin block');
    await user.type(screen.getByLabelText('Description'), 'Projector will not connect to any laptop.');
}

describe('RequestForm', () => {
    // TC-U-08 | FR-001 | negative: an empty form is blocked on the client and shows every error
    it('blocks an empty submission and shows errors', async () => {
        const onSubmit = vi.fn();
        const user = userEvent.setup();
        render(<RequestForm categories={categories} onSubmit={onSubmit} />);

        await user.click(screen.getByRole('button', { name: 'Submit request' }));

        expect(onSubmit).not.toHaveBeenCalled();
        expect(screen.getAllByRole('alert')).toHaveLength(4);
        expect(screen.getByLabelText('Description')).toHaveAttribute('aria-invalid', 'true');
    });

    // TC-U-09 | FR-001 | a valid form submits trimmed values with a numeric category id
    it('submits clean values when valid', async () => {
        const onSubmit = vi.fn();
        const user = userEvent.setup();
        render(<RequestForm categories={categories} onSubmit={onSubmit} />);

        await fillValid(user);
        await user.click(screen.getByRole('button', { name: 'Submit request' }));

        expect(onSubmit).toHaveBeenCalledWith({
            category_id: 2,
            location: 'Room 204',
            area: 'Admin block',
            description: 'Projector will not connect to any laptop.',
        });
        expect(screen.queryAllByRole('alert')).toHaveLength(0);
    });

    // TC-U-10 | FR-001 | the live character counter helps the user stay inside the limits
    it('counts description characters', async () => {
        const user = userEvent.setup();
        render(<RequestForm categories={categories} onSubmit={() => {}} />);
        await user.type(screen.getByLabelText('Description'), 'hello');
        expect(screen.getByText(/5 \/ 2000 characters/)).toBeInTheDocument();
    });

    // TC-U-11 | server-side messages (which the browser cannot know) are displayed
    it('shows server validation errors', () => {
        render(<RequestForm categories={categories} onSubmit={() => {}} serverErrors={{ category_id: 'Choose a valid category.' }} />);
        expect(screen.getByRole('alert')).toHaveTextContent('Choose a valid category.');
    });

    it('disables the button while submitting', () => {
        render(<RequestForm categories={categories} onSubmit={() => {}} processing />);
        expect(screen.getByRole('button', { name: 'Submitting…' })).toBeDisabled();
    });
});
