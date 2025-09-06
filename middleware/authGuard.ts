// project-root/middleware/authGuard.ts

export function authGuard(token: string): boolean {
  return token === 'valid-token'; // Replace with real logic
}
