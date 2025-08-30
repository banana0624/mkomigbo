// project-root/src/utils/audit/logTransition.ts

import type { BackupState } from '../../types/backup/backupTypes';
import { getAuditLog } from './auditStore';
import { useContributorToggles } from '../../hooks/useContributorToggles';

export function logTransition(entryId: string, from: BackupState, to: BackupState, contributor: string) {
  const { dryRunEnabled } = useContributorToggles();
  const auditLog = getAuditLog();

  auditLog.push({
    entryId,
    from,
    to,
    timestamp: Date.now(),
    dryRun: dryRunEnabled,
    contributor,
  });
}