// project-root/subjects/language1/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const language1manifest = manifestRegistry.subjects.get('language1');

export default function Language1Page() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!language1manifest) return <div>Language1 content not found.</div>;

  return (
    <main>
      <h1>{language1manifest.title}</h1>
      <p>{language1manifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
} 
