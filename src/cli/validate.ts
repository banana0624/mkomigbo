// project-root/src/cli/validate.ts

import { Command } from 'commander';
import { runValidation } from '../../scripts/validate-core';

const program = new Command();

program
  .name('cli validate')
  .description('Validate CLI modules for missing exports, broken paths, and locate files')
  .option('--dry-run', 'Run without printing detailed output')
  .option('--summary-only', 'Only show summary, no details')
  .argument('[filename]', 'Optional file to locate anywhere in the project')
  .action((filename, options) => {
    runValidation({
      dryRun: options.dryRun,
      summaryOnly: options.summaryOnly,
      desiredFileName: filename,
    });
  });

program.parse(process.argv);
