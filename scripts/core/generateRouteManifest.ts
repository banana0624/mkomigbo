// project-root/scripts/core/generateRouteManifest.ts

export const generateRouteManifest = (routes: string[]): Record<string, string> => {
  return routes.reduce((manifest, route) => {
    manifest[route] = `/generated/${route}`;
    return manifest;
  }, {} as Record<string, string>);
};
