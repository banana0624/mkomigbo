// project-root/subjects/spirituality/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const strugglemanifest = manifestRegistry.subjects.get('struggle');

export default function StrugglePage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!strugglemanifest) return <div>Struggle content not found.</div>;

  return (
    <main>
      <h1>{strugglemanifest.title}</h1>
      <p>{strugglemanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}