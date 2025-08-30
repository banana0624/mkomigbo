// project-root/subjects/religion/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const religionmanifest = manifestRegistry.subjects.get('religion');

export default function ReligionPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!religionmanifest) return <div>Religion content not found.</div>;

  return (
    <main>
      <h1>{religionmanifest.title}</h1>
      <p>{religionmanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}