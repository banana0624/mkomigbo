// src/components/BadgeDisplay.tsx

import styles from '../styles/animations.module.css';
import { badgeData } from '../data/badgeData';
import { useBadgePulse } from '../hooks/useBadgePulse';

interface Props {
  unlockedBadgeIds: string[];
}

export function BadgeDisplay({ unlockedBadgeIds }: Props) {
  return (
    <div className="badge-grid">
      {badgeData.map((badge) => {
        const isUnlocked = unlockedBadgeIds.includes(badge.id);
        const pulseClass = useBadgePulse(isUnlocked);

        return (
          <div key={badge.id} className={`badge-card ${isUnlocked ? styles[pulseClass] : ''}`}>
            <img src={badge.icon} alt={badge.label} />
            <h4>{badge.label}</h4>
            <p>{badge.description}</p>
            <small>{badge.milestone}</small>
          </div>
        );
      })}
    </div>
  );
}
