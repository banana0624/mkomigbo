// project-root/subjects/europe/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const europemanifest = manifestRegistry.subjects.get('europe');

export default function EuropePage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!europemanifest) return <div>Europe content not found.</div>;

  return (
    <main>
      <h1>{europemanifest.title}</h1>
      <p>{europemanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}