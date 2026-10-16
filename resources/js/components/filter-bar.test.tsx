import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import FilterBar from './filter-bar';

const categories = [
    { id: 1, name: 'Facility fault' },
    { id: 2, name: 'IT support' },
];
const statuses = ['open', 'assigned', 'in_progress', 'resolved', 'closed'];

describe('FilterBar', () => {
    // TC-U-12 | FR-013 | applying sends only the filled-in fields (combinable filters)
    it('applies only the filled-in filters', async () => {
        const onApply = vi.fn();
        const user = userEvent.setup();
        render(<FilterBar categories={categories} statuses={statuses} onApply={onApply} showMine />);

        await user.selectOptions(screen.getByLabelText('Status'), 'open');
        await user.selectOptions(screen.getByLabelText('Category'), '1');
        await user.click(screen.getByLabelText('Assigned to me'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(onApply).toHaveBeenCalledWith({ status: 'open', category_id: '1', mine: true });
    });

    it('reset clears the fields and applies no filters', async () => {
        const onApply = vi.fn();
        const user = userEvent.setup();
        render(<FilterBar filters={{ status: 'closed', q: 'gate' }} categories={categories} statuses={statuses} onApply={onApply} />);

        expect(screen.getByLabelText('Search')).toHaveValue('gate');
        await user.click(screen.getByRole('button', { name: 'Reset' }));

        expect(onApply).toHaveBeenCalledWith({});
        expect(screen.getByLabelText('Search')).toHaveValue('');
        expect(screen.getByLabelText('Status')).toHaveValue('');
    });

    it('hides the "assigned to me" option for requestors', () => {
        render(<FilterBar categories={categories} statuses={statuses} onApply={() => {}} />);
        expect(screen.queryByLabelText('Assigned to me')).not.toBeInTheDocument();
    });

    // TC-U-21 | FR-021, FR-023 | the active and overdue views are sent only when ticked, and keep their state
    it('sends "Active only" and "Overdue only" when ticked', async () => {
        const onApply = vi.fn();
        const user = userEvent.setup();
        render(<FilterBar filters={{ overdue: true }} categories={categories} statuses={statuses} onApply={onApply} />);

        expect(screen.getByLabelText('Overdue only')).toBeChecked();
        expect(screen.getByLabelText('Active only')).not.toBeChecked();

        await user.click(screen.getByLabelText('Active only'));
        await user.click(screen.getByRole('button', { name: 'Apply' }));

        expect(onApply).toHaveBeenCalledWith({ active: true, overdue: true });
    });
});
