// project-root/core/registeries/manifestregistry.ts
export const manifestRegistry = {
    subjects: new Map(),
    styles: new Map(),
    media: new Map(),
};
// Register functions
export function registerSubject(id, manifest) {
    manifestRegistry.subjects.set(id, manifest);
}
export function registerStyle(id, manifest) {
    manifestRegistry.styles.set(id, manifest);
}
export function registerMedia(id, manifest) {
    manifestRegistry.media.set(id, manifest);
}
