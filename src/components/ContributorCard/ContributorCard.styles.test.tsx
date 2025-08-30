// project-root/src/components/ContributorCard/ContributorCard.styles.test.tsx

import { render } from '@testing-library/react';
import ContributorCard from './ContributorCard';

describe('ContributorCard styles', () => {
  it('applies scoped styles without bleed-through', () => {
    const { container } = render(<ContributorCard name="Theo" badge="momentum" />);
    const card = container.querySelector('[data-testid="contributor-card"]');
    expect(card?.className).toMatch(/card/);
    expect(card?.className).toMatch(/momentum/);
  });
});
