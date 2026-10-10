import { describe, expect, it } from 'vitest';
import { cleanFilters, statusLabel, transitionLabel, transitionNeedsNote } from './workflow';

describe('workflow helpers', () => {
    // TC-U-05 | FR-003 | empty filter values are dropped so URLs and requests stay clean
    it('cleanFilters keeps only filled-in values', () => {
        expect(cleanFilters({ q: '', status: 'open', category_id: '', from: null, mine: false, to: '2026-03-12' })).toEqual({
            status: 'open',
            to: '2026-03-12',
        });
    });

    it('cleanFilters keeps a ticked checkbox', () => {
        expect(cleanFilters({ mine: true })).toEqual({ mine: true });
    });

    // TC-U-06 | FR-005 | resolving needs a note; action labels read like actions
    it('only resolving requires a note', () => {
        expect(transitionNeedsNote('resolved')).toBe(true);
        expect(['in_progress', 'closed', 'assigned', 'open'].some(transitionNeedsNote)).toBe(false);
    });

    it('labels transitions and falls back to the status label', () => {
        expect(transitionLabel('assigned', 'in_progress')).toBe('Start work');
        expect(transitionLabel('resolved', 'closed')).toBe('Close request');
        expect(transitionLabel('open', 'closed')).toBe('Closed');
        expect(statusLabel('in_progress')).toBe('In progress');
    });
});
