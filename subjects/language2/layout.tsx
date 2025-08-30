// project-root/subjects/language2/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const language2manifest = manifestRegistry.subjects.get('language2');

export default function language2Page() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!language2manifest) return <div>Language2 content not found.</div>;

  return (
    <main>
      <h1>{language2manifest.title}</h1>
      <p>{language2manifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}