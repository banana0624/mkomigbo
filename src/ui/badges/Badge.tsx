// project-root/src/ui/badges/Badge.tsx

import React from 'react';
import './BadgePulse.css';

type Props = {
  label: string;
  value: number;
  unit?: string;
  pulse?: boolean;
  theme?: 'complete' | 'pending';
  clarity?: 'excellent' | 'moderate' | 'low';
};

// 🎯 Full-featured badge with pulse, theme, and clarity
export function Badge({
  label,
  value,
  unit = '%',
  pulse = false,
  theme,
  clarity,
}: Props) {
  const good = value >= 95;
  const med = value >= 75 && value < 95;
  const cls = good ? 'badge good' : med ? 'badge med' : 'badge low';
  const clarityClass =
    clarity === 'excellent' ? 'clarity-glow' :
    clarity === 'moderate' ? 'clarity-fade' : '';
  const themeClass = theme === 'complete' ? 'theme-pulse' : '';
  const pulseClass = pulse ? 'pulse' : '';

  return (
    <div className={`${cls} ${pulseClass} ${themeClass} ${clarityClass}`} aria-label={`${label}: ${value}${unit}`}>
      <span className="badge-label">{label}</span>
      <span className="badge-value">{value}{unit}</span>
    </div>
  );
}

// 🧩 Simplified badge variant for fallback or minimal display
export function SimpleBadge({
  label,
  value,
  unit = '%',
}: Props) {
  const good = value >= 95;
  const med = value >= 75 && value < 95;
  const cls = good ? 'badge good' : med ? 'badge med' : 'badge low';
  const pulseClass = value === 100 ? 'pulse' : '';

  return (
    <div className={`${cls} ${pulseClass}`} aria-label={`${label}: ${value}${unit}`}>
      <span className="badge-label">{label}</span>
      <span className="badge-value">{value}{unit}</span>
    </div>
  );
}
