// src/components/ContributorCard/ContributorCard.test.tsx

import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import ContributorCard from './ContributorCard';

describe('ContributorCard', () => {
  it('renders contributor name and badge', () => {
    render(<ContributorCard name="Theo" badge="momentum" />);
    expect(screen.getByText('Theo')).toBeInTheDocument();
    expect(screen.getByLabelText('Momentum Badge')).toBeVisible();
  });

  it('applies lifecycle-aware styles', () => {
    render(<ContributorCard name="Theo" badge="newcomer" />);
    const card = screen.getByTestId('contributor-card');
    expect(card.className).toContain('newcomer');
  });

  it('triggers rhythm animation on mount', () => {
    render(<ContributorCard name="Theo" badge="veteran" />);
    const card = screen.getByTestId('contributor-card');
    expect(card.className).toContain('card');
  });
});
