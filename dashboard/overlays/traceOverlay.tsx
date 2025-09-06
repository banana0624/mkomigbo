// project-root/dashboard/overlays/traceOverlay.tsx

import React from 'react';

export function TraceOverlay({ message }: { message: string }) {
  return (
    <div style={{ background: '#222', color: '#fff', padding: '1rem' }}>
      <strong>Trace:</strong> {message}
    </div>
  );
}
