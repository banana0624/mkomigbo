// project-root/src/models/BackupState.ts

/** Represents the lifecycle state of a backup entry. */
export type BackupState = 'pending' | 'completed' | 'failed' | 'archived';

/** All valid backup states for validation and filtering. */
export const BACKUP_STATES: BackupState[] = [
  'pending',
  'completed',
  'failed',
  'archived',
];

/**
 * Checks if a given value is a valid BackupState.
 * Useful for validation, filtering, and audit tracing.
 */
export function isValidBackupState(state: string): state is BackupState {
  return BACKUP_STATES.includes(state as BackupState);
}
