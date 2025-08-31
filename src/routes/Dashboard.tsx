// project-root/src/pages/Dashboard.tsx

import React, { useState } from 'react';
import { AuditViewer } from '../components/audit/AuditViewer.tsx';
import { LifecycleTimeline } from '../components/timeline/LifecycleTimeline.tsx';
import { AuditFilters } from '../components/filters/AuditFilters.tsx';
import { ContributorSummary } from '../components/summary/ContributorSummary.tsx';
import { DashboardSidebar } from '../components/DashboardSidebar.tsx';
import { FilteredCountIndicator } from '../components/summary/FilteredCountIndicator.tsx';
import { useAuditFilters } from '../hooks/useAuditFilters.ts';
import { useFilteredAuditEntries } from '../hooks/useFilteredAuditEntries.ts';
import { validBackupStates } from '../constants/backupStates.ts';
import { getAuditLog } from '../utils/audit/auditStore.ts';
import { FilterSummaryChip } from '../components/filters/FilterSummaryChip.tsx';
import type { BackupEntry, BackupState } from '../types/backup/backupTypes.ts';
import type { AuditEntry } from '../types/audit/auditTypes.ts';
import { ContributorStreaks } from '../components/summary/ContributorStreaks.tsx';
import { OnboardingOverlay } from '../components/onboarding/OnboardingOverlay.tsx';

const Dashboard: React.FC = () => {
  const auditEntries: AuditEntry[] = getAuditLog();

  const {
    contributor,
    stage,
    startDate,
    endDate,
    setContributor,
    setStage,
    setStartDate,
    setEndDate,
  } = useAuditFilters();

  const [dryRunOnly, setDryRunOnly] = useState(false);

  const contributors = Array.from(new Set(auditEntries.map((e) => e.contributor)));
  const filters = { contributor, stage, startDate, endDate, dryRunOnly };
  const filteredEntries = useFilteredAuditEntries(auditEntries, filters);

  const timelineEntries: BackupEntry[] = filteredEntries.map((entry) => ({
    id: entry.id,
    state: entry.to as BackupState,
    timestamp: entry.timestamp.toString(), // ✅ Only one timestamp field
    contributorId: 'c001', // or derive dynamically
    size: '1.2 GB',
    status: 'pending',
    stage: entry.state,
  }));

  const exportJSON = () => {
    const blob = new Blob([JSON.stringify(filteredEntries, null, 2)], {
      type: 'application/json',
    });
    const url = URL.createObjectURL(blob);
    triggerDownload(url, 'audit-snapshot.json');
  };

  const exportCSV = () => {
    const header = ['Entry ID', 'From', 'To', 'Timestamp', 'Dry Run', 'Contributor'];
    const rows = filteredEntries.map((entry) => [
      entry.id,
      entry.from,
      entry.to,
      new Date(entry.timestamp).toISOString(),
      entry.dryRun ? 'Yes' : 'No',
      entry.contributor,
    ]);
    const csvContent = [header, ...rows].map((r) => r.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    triggerDownload(url, 'audit-snapshot.csv');
  };

  const triggerDownload = (url: string, filename: string) => {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.click();
    URL.revokeObjectURL(url);
  };

  return (
    <>
      <OnboardingOverlay />

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '2rem' }}>
        <div style={{ flex: '2 1 600px' }}>
          <FilterSummaryChip
            contributor={contributor}
            stage={stage}
            startDate={startDate}
            endDate={endDate}
            dryRunOnly={dryRunOnly}
            onReset={() => {
              setContributor(null);
              setStage(null);
              setStartDate(null);
              setEndDate(null);
              setDryRunOnly(false);
            }}
          />

          <div id="filters">
            <AuditFilters
              contributors={contributors}
              stages={validBackupStates}
              selectedContributor={contributor}
              selectedStage={stage}
              startDate={startDate}
              endDate={endDate}
              onContributorChange={setContributor}
              onStageChange={setStage}
              onStartDateChange={setStartDate}
              onEndDateChange={setEndDate}
              dryRunOnly={dryRunOnly}
              onDryRunToggle={setDryRunOnly}
            />
          </div>

          <FilteredCountIndicator count={filteredEntries.length} />

          <div id="viewer">
            <AuditViewer entries={filteredEntries} />
          </div>

          <div id="timeline">
            <LifecycleTimeline entries={timelineEntries} />
          </div>

          <ContributorSummary entries={filteredEntries} />
          <ContributorStreaks entries={filteredEntries} />

          <div style={{ marginTop: '2rem' }}>
            <button onClick={exportJSON}>Export JSON Snapshot</button>
            <button onClick={exportCSV} style={{ marginLeft: '1rem' }}>
              Export CSV Snapshot
            </button>
          </div>
        </div>

        <div id="sidebar" style={{ flex: '1 1 300px' }}>
          <DashboardSidebar />
        </div>
      </div>
    </>
  );
};

export default Dashboard;