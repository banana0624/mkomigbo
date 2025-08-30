// project-root/src/cli.ts

import { Command } from 'commander';
import {
  setVerbose,
  enableFileLogging,
  logLifecycle,
  logDryRun
} from '../scripts/cli/utils/logger';

import { initLoggerFromCLI } from '../scripts/cli/utils/init-logger';
import { loadBackups } from '../scripts/cli/utils/loadBackups';
import { summarizeBackups } from '../scripts/cli/utils/backup-utils';
import { BackupEntry } from '../scripts/types/backup';

import yargs from 'yargs';

const argv = yargs(process.argv.slice(2))
  .option('summarize', {
    type: 'boolean',
    description: 'Summarize backup contents after execution',
    default: false,
  })
  .option('dryRun', {
    type: 'boolean',
    description: 'Simulate actions without executing them',
    default: false,
  })
  .help()
  .parseSync(); // ✅ Fixes type error

// Initialize logger from CLI flags
initLoggerFromCLI();

// Load and validate backups
const backups: BackupEntry[] = loadBackups(argv.dryRun);

// Conditionally summarize backups
if (argv.summarize && !argv.dryRun) {
  summarizeBackups(backups);
}

// Lifecycle logs
logLifecycle('init', 'Starting CLI process');
logLifecycle('validate', 'Config validated successfully', 'debug');
logLifecycle('error', 'Missing config keys: output', 'error');

// Dry-run example
if (argv.dryRun) {
  logDryRun('delete', 'dist/');
}

// CLI options setup (optional if using initLoggerFromCLI)
const program = new Command();
program
  .option('--verbose', 'Enable verbose logging')
  .option('--log-file', 'Enable file-based logging')
  .parse(process.argv);

const options = program.opts();
// Already handled by initLoggerFromCLI

// Optional: use backups in your CLI logic
if (backups.length === 0) {
  logLifecycle('warn', 'No backups found or failed to load', 'warn');
} else {
  logLifecycle('info', `Ready to process ${backups.length} backups`, 'info');
}
