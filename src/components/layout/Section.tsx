// project-root/src/components/layout/Section.tsx

import React from 'react';

interface SectionProps {
  title: string;
  children: React.ReactNode;
}

export const Section: React.FC<SectionProps> = ({ title, children }) => (
  <section style={{ marginBottom: '24px' }}>
    <h2 style={{ fontSize: '1.2rem', marginBottom: '12px' }}>{title}</h2>
    <div>{children}</div>
  </section>
);

