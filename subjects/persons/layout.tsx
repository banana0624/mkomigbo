// project-root/subjects/persons/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const personsmanifest = manifestRegistry.subjects.get('persons');

export default function PersonsPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!personsmanifest) return <div>Persons content not found.</div>;

  return (
    <main>
      <h1>{personsmanifest.title}</h1>
      <p>{personsmanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}