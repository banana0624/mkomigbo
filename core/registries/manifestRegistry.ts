// project-root/core/registeries/manifestRegistry.ts

// Central registry for module manifests
import { SubjectManifest, StyleManifest, MediaManifest } from './types.js';

export const manifestRegistry = {
  subjects: new Map(),
  styles: new Map(),
  media: new Map(),
  register: (key: string, manifest: object) => {
    manifestRegistry.subjects.set(key, manifest);
  },
};


// Register functions
export function registerSubject(id: string, manifest: SubjectManifest) {
  manifestRegistry.subjects.set(id, manifest);
}

export function registerStyle(id: string, manifest: StyleManifest) {
  manifestRegistry.styles.set(id, manifest);
}

export function registerMedia(id: string, manifest: MediaManifest) {
  manifestRegistry.media.set(id, manifest);
}
