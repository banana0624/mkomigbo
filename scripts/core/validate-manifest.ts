// project-root/scripts/core/validate-manifest.ts

export const validateManifest = (manifest: Record<string, string>): string[] => {
  return Object.entries(manifest)
    .filter(([key, value]) => !value.startsWith('/generated/'))
    .map(([key]) => `Invalid route path for ${key}`);
};
