// project-root/scripts/core/validate-core.ts

export const validateCoreConfig = (config: Record<string, unknown>): string[] => {
  const errors: string[] = [];
  if (!config['entry']) errors.push('Missing entry point');
  if (!config['output']) errors.push('Missing output directory');
  return errors;
};
