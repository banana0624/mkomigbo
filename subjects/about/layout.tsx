// project-root/subjects/about/layout.tsx

import { useEffect } from 'react';
import { Helmet } from 'react-helmet';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';

const aboutmanifest = manifestRegistry.subjects.get('about');

export default function AboutPage() {
  useEffect(() => {
    lifecycleRegistry.onInit.forEach(hook => hook());
    return () => {
      lifecycleRegistry.onDestroy.forEach(hook => hook());
    };
  }, []);

  if (!aboutmanifest) return <div>About content not found.</div>;

  const { title, description, meta } = aboutmanifest;

  return (
    <>
      <Helmet>
        <title>{meta?.pageTitle || title}</title>
        <meta name="description" content={meta?.description || description} />
        <meta name="keywords" content={meta?.keywords?.join(', ') || ''} />
        <meta name="subject" content={meta?.subject || title} />
      </Helmet>

      <main>
        <h1>{title}</h1>
        <p>{description}</p>
        {/* Future: Render styles/media dynamically */}
      </main>
    </>
  );
}
