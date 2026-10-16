import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import AvgResolutionCard from './avg-resolution-card';

describe('AvgResolutionCard', () => {
    // TC-U-17 | FR-024 | the dashboard shows the average resolution time in readable form
    it('shows the average as days and hours', () => {
        render(<AvgResolutionCard hours={52} />);
        expect(screen.getByRole('heading', { name: 'Average resolution time' })).toBeInTheDocument();
        expect(screen.getByTestId('avg-resolution')).toHaveTextContent('2 d 4 h');
    });

    // TC-U-18 | FR-024 | negative: nothing resolved yet must not show a fake zero
    it.each([[null], [undefined], [Number.NaN]])('shows "No data" instead of a number for %s', (hours) => {
        render(<AvgResolutionCard hours={hours} />);
        expect(screen.getByTestId('avg-resolution')).toHaveTextContent('No data');
        expect(screen.getByText('None of the requests submitted in this period have been resolved yet.')).toBeInTheDocument();
    });
});
