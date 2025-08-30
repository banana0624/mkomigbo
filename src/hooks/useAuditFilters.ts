// project-root/src/hooks/useAuditFilters.ts

import { useState } from 'react';

const [dryRunOnly, setDryRunOnly] = useState(false);

export function useAuditFilters() {
  const [contributor, setContributor] = useState<string | null>(null);
  const [stage, setStage] = useState<string | null>(null);
  const [startDate, setStartDate] = useState<string | null>(null);
  const [endDate, setEndDate] = useState<string | null>(null);

  return {
  contributor,
  stage,
  startDate,
  endDate,
  dryRunOnly,
  setContributor,
  setStage,
  setStartDate,
  setEndDate,
  setDryRunOnly,
};
}