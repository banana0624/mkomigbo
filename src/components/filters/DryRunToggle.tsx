// project-root/src/components/filters/DryRunToggle.tsx

import React from 'react';

interface Props {
  dryRunOnly: boolean;
  setDryRunOnly: (value: boolean) => void;
}

export const DryRunToggle: React.FC<Props> = ({ dryRunOnly, setDryRunOnly }) => (
  <label style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
    <input
      type="checkbox"
      checked={dryRunOnly}
      onChange={(e) => setDryRunOnly(e.target.checked)}
    />
    Show Dry-Run Only
  </label>
);