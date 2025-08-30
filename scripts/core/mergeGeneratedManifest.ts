// project-root/scripts/core/mergeGeneratedManifest.ts

export const mergeGeneratedManifest = (
  base: Record<string, string>,
  generated: Record<string, string>
): Record<string, string> => {
  return { ...base, ...generated };
};
