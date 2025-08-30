// project-root/scripts/types/manifest.d.ts

export interface Manifest {
  name: string;
  role: string;
  module?: string;
  hooks?: {
    onInit?: () => void;
    onDestroy?: () => void;
    [key: string]: () => void;
  };
  metadata?: Record<string, any>;
}