// project-root/src/wrappers/ContributorInsightsWrapper.tsx

import React from 'react';
import { RatioMeter } from '../components/RatioMeter';
import { AuditHeatmap } from '../components/AuditHeatmap';
import { ContributorRhythm } from '../components/ContributorRhythm';
import { Section } from '../components/Section';
import { SectionLegend } from '../components/SectionLegend';
import { AuditEntry } from '../models/AuditEntry';

interface ContributorInsightsWrapperProps {
  contributorId: string;
  auditEntries: AuditEntry[];
  streakCount: number;
  dryRunRatio: number;
}

export const ContributorInsightsWrapper: React.FC<ContributorInsightsWrapperProps> = ({
  contributorId,
  auditEntries,
  streakCount,
  dryRunRatio,
}) => {
  return (
    <Section title="Contributor Insights">
      <SectionLegend items={[{ label: 'Dry Run', color: '#007bff' }]} />
      <ContributorRhythm pulses={[]} />
      <RatioMeter label="Dry Run Ratio" numerator={streakCount} denominator={10} />
      <AuditHeatmap entries={auditEntries} />
    </Section>
  );
};
