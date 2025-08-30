// project-root/subjects/africa/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const africamanifest = manifestRegistry.subjects.get('africa');

export default function AfricaPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!africamanifest) return <div>Africa content not found.</div>;

  return (
    <main>
      <h1>{africamanifest.title}</h1>
      <p>{africamanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}