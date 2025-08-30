// project-root/scripts/core/validate-cli.ts

import { CLIFlag } from '../cli/flags/cliFlag';

export const validateCLIFlags = (flags: Record<string, unknown>, schema: CLIFlag[]): string[] => {
  return schema
    .filter(flag => flag.required && !(flag.name in flags))
    .map(flag => `Missing required flag: --${flag.name}`);
};
