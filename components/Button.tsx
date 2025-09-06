// project-root/components/Button.tsx

import React from 'react';

export function Button({ label, onClick }: { label: string; onClick: () => void }) {
  return <button onClick={onClick}>{label}</button>;
}
