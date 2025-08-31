// project-root/src/utils/audit/logTransition.ts

import type { BackupState } from '../../types/backup/backupTypes.ts';
import { getAuditLog } from './auditStore.ts';
import { useContributorToggles } from '../../hooks/useContributorToggles.ts';

export interface AuditEntry {
  entryId: string;
  // other fields...
}

export function logTransition(
  entryId: string,
  from: BackupState,
  to: BackupState,
  contributor: string
) {
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
