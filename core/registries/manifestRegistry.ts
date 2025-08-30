// project-root/core/registeries/manifestregistry.ts

// Central registry for module manifests
import { SubjectManifest, StyleManifest, MediaManifest } from './types';

export const manifestRegistry = {
  subjects: new Map<string, SubjectManifest>(),
  styles: new Map<string, StyleManifest>(),
  media: new Map<string, MediaManifest>(),
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
