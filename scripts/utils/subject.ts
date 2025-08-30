// project-root/scripts/utils/subject.ts

export const getSubjectFromPath = (path: string): string => {
  const segments = path.split('/');
  return segments.includes('subjects') ? segments[segments.indexOf('subjects') + 1] : 'unknown';
};

export const isSubjectRoute = (path: string): boolean =>
  path.includes('/subjects/');

/** Subject identity and selection. */
export function getCurrentSubject(): string { return 'default'; } // TODO
export function setCurrentSubject(subject: string): void { /* TODO */ }

