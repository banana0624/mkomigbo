// project-root/subjects/spirituality/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const spiritualitymanifest = manifestRegistry.subjects.get('spirituality');

export default function SpiritualityPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!spiritualitymanifest) return <div> Spirituality content not found.</div>;

  return (
    <main>
      <h1>{spiritualitymanifest.title}</h1>
      <p>{spiritualitymanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}