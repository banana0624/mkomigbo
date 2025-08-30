// project-root/scripts/forms/passwordStrength.ts

export type PasswordStrength = 'weak' | 'medium' | 'strong';

export const evaluatePasswordStrength = (password: string): PasswordStrength => {
  const lengthScore = password.length >= 12 ? 2 : password.length >= 8 ? 1 : 0;
  const hasSymbol = /[^a-zA-Z0-9]/.test(password);
  const hasNumber = /\d/.test(password);
  const hasMixedCase = /[a-z]/.test(password) && /[A-Z]/.test(password);

  const score = lengthScore + Number(hasSymbol) + Number(hasNumber) + Number(hasMixedCase);

  if (score >= 4) return 'strong';
  if (score >= 2) return 'medium';
  return 'weak';
};
