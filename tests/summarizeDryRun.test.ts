// project-root/tests/summarizeDryRun.test.ts

import { describe, it, expect } from 'vitest';
import { summarizeBackupsDryRun } from '../src/backups/summarizeDryRun';

describe('summarizeBackupsDryRun', () => {
  it('returns a mock backup summary', async () => {
    const summary = await summarizeBackupsDryRun();
    expect(summary.total).toBeGreaterThan(0);
    expect(summary.latest).toMatch(/^\d{4}-\d{2}-\d{2}T/);
    expect(summary.sizeEstimate).toMatch(/\d+(\.\d+)?\sGB/);
  });
});