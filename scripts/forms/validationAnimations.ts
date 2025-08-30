// project-root/scripts/validationAnimations.ts

export const triggerValidationAnimation = (elementId: string, status: 'valid' | 'invalid') => {
  const el = document.getElementById(elementId);
  if (!el) return;

  el.classList.remove('valid', 'invalid');
  el.classList.add(status);

  setTimeout(() => el.classList.remove(status), 1000);
};
