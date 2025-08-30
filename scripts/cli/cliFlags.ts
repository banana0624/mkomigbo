// project-root/scripts/utils/cliFlags.ts

/**
 * 🏁 Shared CLI flags utility
 * Centralizes parsing of common flags for all CLI scripts
 */

const rawArgs = process.argv.slice(2);

export const cliFlags = {
  force: rawArgs.includes('--force'),
  dryRun: rawArgs.includes('--dry-run'),
  verbose: rawArgs.includes('--verbose'),
  help: rawArgs.includes('--help'),
  // Extend with more flags as needed
};
