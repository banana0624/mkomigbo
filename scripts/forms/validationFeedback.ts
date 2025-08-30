// project-root/scripts/forms/validationFeedback.ts

import { pushFeedback } from '../utils/feedback';

export const showValidationFeedback = (
  field: string,
  status: 'valid' | 'invalid',
  message?: string
): void => {
  const level = status === 'valid' ? 'success' : 'error';
  const text = message ?? (status === 'valid' ? `${field} looks good` : `${field} is invalid`);
  pushFeedback(text, level, `validation:${field}`);
};
