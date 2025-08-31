// project-root/src/components/timeline/LifecycleTimeline.tsx

import React from 'react';
import type { BackupEntry } from '../../types/backup/backupTypes';
import styles from './LifecycleTimeline.module.css';
import { useLifecycleAnimation } from '../../hooks/useLifecycleAnimation';

interface Props {
  entries: BackupEntry[];
}

const uniqueStages = Array.from(new Set(entries.map((e) => e.stage)));
const animatedStage = useLifecycleAnimation(entry.stage);
<span>{entry.stage}</span>

const stateDescriptions: Record<string, string> = {
  INIT: 'Initial state',
  ACTIVE: 'Active backup',
  ARCHIVED: 'Archived state',
  ERROR: 'Failed or incomplete',
  RESTORED: 'Restored from backup',
};

export const LifecycleTimeline: React.FC<Props> = ({ entries }) => {
  if (entries.length === 0) {
    return <div className={styles.empty}>No lifecycle entries available.</div>;
  }

  const uniqueStates: string[] = Array.from(new Set(entries.map((e) => e.state)));

  return (
    <div className={styles.timeline}>
      <h2>Backup Lifecycle Timeline</h2>
      {entries.map((entry) => {
        const animatedState = useLifecycleAnimation(entry.state);

        return (
          <div key={entry.id} className={styles.entry}>
            <div className={styles.id}>{entry.id}</div>
            <div className={styles.track}>
              <span className={`${styles.state} ${styles[animatedState]}`}>{entry.state}</span>
              <span className={styles.timestamp}>
                {new Date(entry.timestamp).toLocaleString()}
              </span>
            </div>
          </div>
        );
      })}

      <div className={styles.legend}>
        <strong>Legend:</strong>
        <ul>
          {uniqueStates.map((state: string) => (
            <li key={state}>
              <span className={styles.state}>{state}</span> – {stateDescriptions[state] || 'Unknown'}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
};
