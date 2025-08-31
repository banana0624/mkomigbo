// project-root/src/components/BadgeDisplay/__tests__/BadgeDisplay.snapshot.test.tsx

import { render } from '@testing-library/react';
import BadgeDisplay from '../BadgeDisplay';

describe('BadgeDisplay snapshot', () => {
  it('renders correctly', () => {
    const { container } = render(<BadgeDisplay badge="momentum" />);
    expect(container).toMatchSnapshot();
  });
});
