// project-root/scripts/utils/themeTokens.ts

/** Core theme colors for UI surfaces and states. */
export const themeColors = {
  primary: '#3a7afe',
  success: '#16a34a',
  warning: '#f59e0b',
  error: '#ef4444',
  background: '#f9fafb',
  surface: '#ffffff',
  text: '#1f2937',
};

/** Spacing tokens in pixels for layout rhythm. */
export const spacingPx = {
  xs: '4px',
  sm: '8px',
  md: '12px',
  lg: '16px',
  xl: '24px',
};

/** Numeric spacing tokens for calculations and animation. */
export const spacingNum = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
};

/** Rhythm timing for animations and lifecycle ticks. */
export const rhythm = {
  beat: 400,   // ms per rhythm tick
  pulse: 1200, // ms for badge animation
};

/** Subject/platform identity tokens. Expand per theme. */
export const identityTokens = {
  primary: '#000000',
  accent: '#ffffff',
  muted: '#e5e7eb',
  highlight: '#3b82f6',
};
