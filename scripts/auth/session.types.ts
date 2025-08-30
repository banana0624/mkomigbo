// project-root/scripts/auth/session.types.ts

export interface Session {
  userId: string;
  token: string;
  expiresAt: number;
}
