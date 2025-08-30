// project-root/src/components/animations/BackupTransition.tsx

import React, { useState, useEffect } from 'react';
import type { BackupState } from '../../types/backup/backupTypes';
import styles from './BackupTransition.module.css';

const BackupTransition: React.FC<{ from: BackupState; to: BackupState }> = ({ from, to }) => {
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      setProgress((p) => Math.min(p + 10, 100));
    }, 100);
    return () => clearInterval(interval);
  }, []);

  return (
    <div className={styles.transitionContainer}>
      <span>{from}</span>
      <div
        className={styles.progressBar}
        style={{ '--progress': `${progress}%` } as React.CSSProperties}
      />
      <span>{to}</span>
    </div>
  );
};

export default BackupTransition;