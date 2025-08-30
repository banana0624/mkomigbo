// project-root/subjects/history/layout.tsx

import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const historyManifest = manifestRegistry.subjects.get('history');

export default function HistoryPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!historyManifest) return <div>History content not found.</div>;

  return (
    <main>
      <h1>{historyManifest.title}</h1>
      <p>{historyManifest.description}</p>
      {/* Future: Render styles/media dynamically */}
    </main>
  );
}
