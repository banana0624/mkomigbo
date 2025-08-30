// project-root/scripts/auth/tokenManager.ts

export const generateToken = (userId: string): string => {
  return `${userId}-${Date.now()}-${Math.random().toString(36).slice(2)}`;
};

export const decodeToken = (token: string): string => {
  return token.split('-')[0];
};
