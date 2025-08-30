// project-root/subjects/culture/layout.tsx


import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const cultureManifest = manifestRegistry.subjects.get('culture');

export default function CulturePage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!cultureManifest) return <div>Culture content not found.</div>;

  return (
    <main>
      <h1>{cultureManifest.title}</h1>
      <p>{cultureManifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}
