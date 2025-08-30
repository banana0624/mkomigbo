// project-root/src/components/ContributorCard/ContributorCard.rhythm.test.tsx

import { render } from '@testing-library/react';
import ContributorCard from './ContributorCard';

describe('ContributorCard rhythm states', () => {
  it('applies newcomer rhythm style', () => {
    const { container } = render(<ContributorCard name="Theo" badge="newcomer" />);
    const card = container.querySelector('[data-testid="contributor-card"]');
    expect(card?.className).toContain('newcomer');
  });

  it('applies momentum rhythm style', () => {
    const { container } = render(<ContributorCard name="Theo" badge="momentum" />);
    const card = container.querySelector('[data-testid="contributor-card"]');
    expect(card?.className).toContain('momentum');
  });

  it('applies veteran rhythm style', () => {
    const { container } = render(<ContributorCard name="Theo" badge="veteran" />);
    const card = container.querySelector('[data-testid="contributor-card"]');
    expect(card?.className).toContain('veteran');
  });
});
