// project-root/scripts/auth/sessionCheck.ts

import { Session } from './session.types';

export const isSessionValid = (session: Session): boolean => {
  return Date.now() < session.expiresAt;
};
