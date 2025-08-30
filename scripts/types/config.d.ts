// project-root/scripts/types/config.d.ts

export interface CLIConfig {
  defaultManifestPath: string;
  enableAuditTrail: boolean;
  trashExpiryDays?: number;
  restoreBackupPath?: string;
}