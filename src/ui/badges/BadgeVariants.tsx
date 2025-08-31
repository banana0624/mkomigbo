// project-root/src/ui/badges/BadgeVariants.tsx

import React from 'react';
import { Badge } from './Badge.js';

type Intensity = 'low' | 'moderate' | 'high';

function toIntensity(percent: number): Intensity {
  if (percent >= 95) return 'high';
  if (percent >= 75) return 'moderate';
  return 'low';
}

export function AuditBadge({ compliance }: { compliance: number }) {
  const intensity = toIntensity(compliance);
  const pulse = compliance === 100;
  const theme = compliance === 100 ? 'complete' : 'pending';
  const clarity = intensity === 'high' ? 'excellent' : intensity === 'moderate' ? 'moderate' : 'low';
  return (
    <Badge
      label=".js extension compliance"
      value={compliance}
      unit="%"
      pulse={pulse}
      theme={theme}
      clarity={clarity}
    />
  );
}

export function ClarityBadge({ score }: { score: 'low' | 'moderate' | 'excellent' }) {
  const value = score === 'excellent' ? 100 : score === 'moderate' ? 80 : 40;
  return <Badge label="Directory clarity" value={value} unit="%" clarity={score} />;
}

export function PulseBadge({ label, value }: { label: string; value: number }) {
  return <Badge label={label} value={value} pulse={value === 100} />;
}
