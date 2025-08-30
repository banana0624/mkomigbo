// project-root/src/browser/utils/TypedDataSet.ts

import { DataSet } from 'vis-data';

export interface BackupNode {
  id: string;
  content: string;
  start: string;
}

export function createBackupDataSet(entries: BackupNode[]): DataSet<BackupNode> {
  return new DataSet<BackupNode>(entries);
}