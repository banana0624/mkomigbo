// project-root/app/layout.tsx

import React from 'react';
import { MilestoneBoard } from '@dashboard/ui/MilestoneBoard';

export default function Layout({ children }: { children: React.ReactNode }) {
  return (
    <main>
      <MilestoneBoard />
      <section>{children}</section>
    </main>
  );
}
