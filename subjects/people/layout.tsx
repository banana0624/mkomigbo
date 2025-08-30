// project-root/subjects/people/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const peoplemanifest = manifestRegistry.subjects.get('people');

export default function PeoplePage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!peoplemanifest) return <div>People content not found.</div>;

  return (
    <main>
      <h1>{peoplemanifest.title}</h1>
      <p>{peoplemanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}