// project-root/subjects/ipob/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const ipobmanifest = manifestRegistry.subjects.get('ipob');

export default function IPOBPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!ipobmanifest) return <div>IPOB content not found.</div>;

  return (
    <main>
      <h1>{ipobmanifest.title}</h1>
      <p>{ipobmanifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}