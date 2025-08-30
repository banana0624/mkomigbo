// project-root/subjects/tradition/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const traditionmanifest = manifestRegistry.subjects.get('tradition');

export default function TraditionPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!traditionmanifest) return <div>Tradition content not found.</div>;

  return (
    <main>
      <h1>{traditionmanifest.title}</h1>
      <p>{traditionmanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}