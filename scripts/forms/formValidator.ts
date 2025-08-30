// project-root/scripts/forms/formValidator.ts

import { defaultFieldConfigs, type FieldConfig } from './fieldConfig';
import { isValidEmail } from './validateEmail';
import { evaluatePasswordStrength } from './passwordStrength';
import { checkUsernameAvailability } from './checkUsername';
import { showValidationFeedback } from './validationFeedback';
import { triggerValidationAnimation } from './validationAnimations';
import { traceOnboardingEvent, rhythmTick } from '../onboarding/auditHeatmap';
import type { ValidationStatus } from '../types';

export interface FieldResult {
  name: string;
  valid: boolean;
  message?: string;
  status: ValidationStatus;
}

export interface FormResult {
  isValid: boolean;
  fields: FieldResult[];
}

const fieldId = (name: string) => `field-${name}`;

export const validateField = async (
  cfg: FieldConfig,
  value: string
): Promise<FieldResult> => {
  const trimmed = (value ?? '').toString().trim();

  // Required
  if (cfg.required && !trimmed) {
    const res: FieldResult = { name: cfg.name, valid: false, status: 'invalid', message: `${cfg.label} is required` };
    emitAudit(cfg.name, res.status, res.message);
    decorateUI(cfg.name, res);
    return res;
  }

  // Min/Max length
  if (cfg.minLength && trimmed.length < cfg.minLength) {
    const res: FieldResult = {
      name: cfg.name,
      valid: false,
      status: 'invalid',
      message: `${cfg.label} must be at least ${cfg.minLength} characters`,
    };
    emitAudit(cfg.name, res.status, res.message);
    decorateUI(cfg.name, res);
    return res;
  }
  if (cfg.maxLength && trimmed.length > cfg.maxLength) {
    const res: FieldResult = {
      name: cfg.name,
      valid: false,
      status: 'invalid',
      message: `${cfg.label} must be at most ${cfg.maxLength} characters`,
    };
    emitAudit(cfg.name, res.status, res.message);
    decorateUI(cfg.name, res);
    return res;
  }

  // Pattern
  if (cfg.pattern && !cfg.pattern.test(trimmed)) {
    const res: FieldResult = { name: cfg.name, valid: false, status: 'invalid', message: `${cfg.label} format is invalid` };
    emitAudit(cfg.name, res.status, res.message);
    decorateUI(cfg.name, res);
    return res;
  }

  // Type-specific checks
  if (cfg.type === 'email' && !isValidEmail(trimmed)) {
    const res: FieldResult = { name: cfg.name, valid: false, status: 'invalid', message: `Please enter a valid email` };
    emitAudit(cfg.name, res.status, res.message);
    decorateUI(cfg.name, res);
    return res;
  }

  if (cfg.type === 'password') {
    const strength = evaluatePasswordStrength(trimmed);
    if (strength === 'weak') {
      const res: FieldResult = {
        name: cfg.name,
        valid: false,
        status: 'invalid',
        message: `Password is too weak`,
      };
      emitAudit(cfg.name, res.status, res.message);
      decorateUI(cfg.name, res);
      return res;
    }
  }

  if (cfg.type === 'username') {
    const available = await checkUsernameAvailability(trimmed);
    if (!available) {
      const res: FieldResult = {
        name: cfg.name,
        valid: false,
        status: 'invalid',
        message: `Username is taken`,
      };
      emitAudit(cfg.name, res.status, res.message);
      decorateUI(cfg.name, res);
      return res;
    }
  }

  // Passed all checks
  const ok: FieldResult = { name: cfg.name, valid: true, status: 'valid', message: `${cfg.label} looks good` };
  emitAudit(cfg.name, ok.status, ok.message);
  decorateUI(cfg.name, ok);
  return ok;
};

export const validateForm = async (
  formData: Record<string, string>,
  configs: FieldConfig[] = defaultFieldConfigs
): Promise<FormResult> => {
  const tasks = configs.map((cfg) => validateField(cfg, formData[cfg.name] ?? ''));
  const results = await Promise.all(tasks);
  const isValid = results.every((r) => r.valid);

  // Emit a high-level audit summary event
  traceOnboardingEvent({
    type: isValid ? 'field_valid' : 'field_invalid',
    field: 'form',
    status: isValid ? 'valid' : 'invalid',
    timestamp: Date.now(),
    rhythmIndex: rhythmTick(),
  } as any);

  return { isValid, fields: results };
};

// Helpers

function emitAudit(field: string, status: ValidationStatus, message?: string) {
  traceOnboardingEvent({
    type: status === 'valid' ? 'field_valid' : 'field_invalid',
    field,
    status,
    timestamp: Date.now(),
    rhythmIndex: rhythmTick(),
  } as any);
}

function decorateUI(name: string, res: FieldResult) {
  // Feedback toast/banner
  const msg = res.message ?? (res.valid ? `${name} ok` : `${name} invalid`);
  try {
    // Side-effect: show contributor-facing message if available
    // Lazy import pattern: keep coupling low if feedback module is optional
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    const { showValidationFeedback } = require('./validationFeedback') as typeof import('./validationFeedback');
    showValidationFeedback(name, res.valid ? 'valid' : 'invalid', msg);
  } catch {
    // no-op if environment doesn't support require
  }

  // Visual animation on the field
  try {
    triggerValidationAnimation(fieldId(name), res.valid ? 'valid' : 'invalid');
  } catch {
    // ignore animation errors in non-DOM contexts
  }
}
