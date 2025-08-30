// project-root/subjects/islam/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const islamManifest = manifestRegistry.subjects.get('islam');

export default function IslamPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!islamManifest) return <div>Islam content not</div>;

  return (
    <main>
      <h1>{islamManifest.title}</h1>
      <p>{islamManifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}