import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import SortControl from './sort-control';

describe('SortControl', () => {
    // TC-U-16 | FR-005, FR-013 | the control shows the current sort and reports a new choice
    it('shows the current choice and calls onChange with the new value', async () => {
        const onChange = vi.fn();
        const user = userEvent.setup();
        render(<SortControl value="created_at:desc" onChange={onChange} />);

        const select = screen.getByLabelText('Sort by');
        expect(select).toHaveValue('created_at:desc');
        expect(screen.getAllByRole('option')).toHaveLength(8);

        await user.selectOptions(select, 'reference:asc');
        expect(onChange).toHaveBeenCalledWith('reference:asc');
    });
});
