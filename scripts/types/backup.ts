// project-root/scripts/types/backup.ts

export type BackupEntry = {
  id: string;
  timestamp: string; // ISO format
  size: number; // in bytes
  files: string[]; // list of file paths
};
