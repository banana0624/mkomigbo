// project-root/src/components/dashboard/ContributorInsights.tsx

import React, { useState } from 'react';
import { ContributorInsightsWrapper } from '../../wrappers/ContributorInsightsWrapper';
import { Section } from '../layout/Section';
import { SectionLegend } from '../layout/SectionLegend';
import { AuditTrail } from '../../routes/audit-trail';
import { AuditEntry } from '../../models/AuditEntry';

export const ContributorInsights: React.FC = () => {
  const [selectedContributor, setSelectedContributor] = useState('currentUser');

  const mockAuditEntries: AuditEntry[] = [
  {
    id: 'entry-001',
    contributor: 'Theo',
    contributorId: 'currentUser',
    timestamp: 1620000000000,
    action: 'dry_run',
  },
  {
    id: 'entry-002',
    contributor: 'Theo',
    contributorId: 'currentUser',
    timestamp: 1620003600000,
    action: 'commit',
  },
];

  return (
    <div style={{ padding: '24px' }}>
      <Section title="Contributor Insights">
        <SectionLegend
          contributorId={selectedContributor}
          onSelectContributor={setSelectedContributor}
        />
        <ContributorInsightsWrapper
          contributorId={selectedContributor}
          auditEntries={mockAuditEntries}
          streakCount={7}
          dryRunRatio={0.82}
        />
        <AuditTrail />
      </Section>
    </div>
  );
};
