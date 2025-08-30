// project-root/src/hooks/useContributorToggles.ts

import { useState } from 'react';

export function useContributorToggles() {
  const [dryRunEnabled, setDryRunEnabled] = useState(false);
  const [summaryOnly, setSummaryOnly] = useState(false);
  const [auditViewerVisible, setAuditViewerVisible] = useState(false);

  return {
    dryRunEnabled,
    summaryOnly,
    auditViewerVisible,
    toggleDryRun: () => setDryRunEnabled((prev) => !prev),
    toggleSummaryOnly: () => setSummaryOnly((prev) => !prev),
    toggleAuditViewer: () => setAuditViewerVisible((prev) => !prev),
  };
}