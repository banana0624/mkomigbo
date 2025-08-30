// project-root/subjects/nigeria/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const nigeriamanifest = manifestRegistry.subjects.get('nigeria');

export default function NigeriaPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!nigeriamanifest) return <div>Nigeria content not found.</div>;

  return (
    <main>
      <h1>{nigeriamanifest.title}</h1>
      <p>{nigeriamanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}