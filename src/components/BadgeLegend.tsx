// src/components/BadgeLegend.tsx

import { badgeData } from '../data/badgeData';

export function BadgeLegend() {
  const tiers = Array.from(new Set(badgeData.map((b) => b.tier)));

  return (
    <div className="legend-wrapper">
      {tiers.map((tier) => (
        <section key={tier} className="legend-section">
          <h3>{tier}</h3>
          <ul>
            {badgeData
              .filter((b) => b.tier === tier)
              .map((b) => (
                <li key={b.id}>
                  <strong>{b.label}</strong>: {b.description}
                </li>
              ))}
          </ul>
        </section>
      ))}
    </div>
  );
}
