// project-root/src/utils/audit/generateAuditId.ts

import { createHash } from 'crypto';
import type { AuditEntry } from '../../types/audit/auditTypes';

export function generateAuditId(entry: AuditEntry): string {
  const raw = `${entry.from}-${entry.to}-${entry.timestamp}-${entry.contributor}-${entry.dryRun}`;
  return createHash('sha256').update(raw).digest('hex').slice(0, 12);
}