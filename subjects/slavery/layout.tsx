// project-root/subjects/slavery/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const slaverymanifest = manifestRegistry.subjects.get('slavery');

export default function SlaveryPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!slaverymanifest) return <div>Slavery content not found.</div>;

  return (
    <main>
      <h1>{slaverymanifest.title}</h1>
      <p>{slaverymanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}