// project-root/tests/cli/summarize.test.ts

import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

const summarizePath = '../../src/cli/commands/summarize.ts';

describe('CLI summarize.ts', () => {
  const originalArgv = process.argv;
  const consoleLogSpy = vi.spyOn(console, 'log').mockImplementation(() => {});

  beforeEach(() => {
    vi.resetModules(); // Clear module cache
  });

  afterEach(() => {
    process.argv = originalArgv;
    consoleLogSpy.mockClear();
  });

  it('runs dry-run with summary-only', async () => {
    process.argv = ['node', 'summarize.ts', '--dry-run', '--summary-only'];
    await import(summarizePath);

    const calls = consoleLogSpy.mock.calls.flat();
    const summaryLine = calls.find(line => line.includes('[SUMMARY-ONLY]'));
    expect(summaryLine).toMatch(/5 backups, latest:/);
  });

  it('runs real summary with full output', async () => {
    process.argv = ['node', 'summarize.ts'];
    await import(summarizePath);

    const calls = consoleLogSpy.mock.calls.flat();
    expect(calls.some(line => line.includes('Total backups:'))).toBe(true);
    expect(calls.some(line => line.includes('Latest backup:'))).toBe(true);
  });
});