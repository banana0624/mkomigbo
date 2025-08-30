// project-root/subjects/uk/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const ukmanifest = manifestRegistry.subjects.get('uk');

export default function UKPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!ukmanifest) return <div>UK content not found.</div>;

  return (
    <main>
      <h1>{ukmanifest.title}</h1>
      <p>{ukmanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}