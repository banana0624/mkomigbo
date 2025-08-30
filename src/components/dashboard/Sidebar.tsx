// project-root/src/components/dashboard/Sidebar.tsx (or similar)

import { ContributorRhythm } from '../visualization';
import { Section } from '../layout/Section'; // Adjust path as needed


<Section title="Contributor Insights">
  <ContributorRhythm streakCount={7} dryRunRatio={0.82} />
</Section>