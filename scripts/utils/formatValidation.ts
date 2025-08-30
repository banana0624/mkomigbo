// project-root/scripts/utils/formatValidation.ts

export const formatValidation = (field: string, value: unknown): string => {
  if (value === null || value === undefined) return `${field} is required`;
  if (typeof value === 'string' && value.trim() === '') return `${field} cannot be empty`;
  return '';
};

/** Ensures values conform to required shape for contributor-facing flows. */
export function validateFormat<T>(value: T): boolean {
  // TODO: implement schema checks
  return Boolean(value);
}
