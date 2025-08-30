// project-root/scripts/cli/flags/cliFlag.ts

export interface CLIFlag {
  name: string;
  description: string;
  required?: boolean;
  defaultValue?: string | boolean;
}

export const parseCLIFlags = (args: string[]): Record<string, string | boolean> => {
  const flags: Record<string, string | boolean> = {};
  args.forEach(arg => {
    const [key, value] = arg.split('=');
    flags[key.replace(/^--/, '')] = value ?? true;
  });
  return flags;
};
