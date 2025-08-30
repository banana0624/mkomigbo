// project-root/src/components/filters/AuditFilters.tsx

import React from 'react';
import styles from './AuditFilters.module.css';

export interface Props {
  contributors: string[];
  stages: string[];
  selectedContributor: string | null;
  selectedStage: string | null;
  startDate: string | null;
  endDate: string | null;
  dryRunOnly: boolean;
  onContributorChange: (value: string | null) => void;
  onStageChange: (value: string | null) => void;
  onStartDateChange: (value: string | null) => void;
  onEndDateChange: (value: string | null) => void;
  onDryRunToggle: (value: boolean) => void;
}

export const AuditFilters: React.FC<Props> = ({
  contributors,
  stages,
  selectedContributor,
  selectedStage,
  startDate,
  endDate,
  dryRunOnly,
  onContributorChange,
  onStageChange,
  onStartDateChange,
  onEndDateChange,
  onDryRunToggle,
}) => {
  return (
    <div className={styles.filters}>
      <label>
        Contributor:
        <select
          value={selectedContributor ?? ''}
          onChange={(e) => onContributorChange(e.target.value || null)}
        >
          <option value="">All</option>
          {contributors.map((name) => (
            <option key={name} value={name}>
              {name}
            </option>
          ))}
        </select>
      </label>

      <label>
        Start Date:
        <input
          type="date"
          value={startDate ?? ''}
          onChange={(e) => onStartDateChange(e.target.value || null)}
        />
      </label>

      <label>
        End Date:
        <input
          type="date"
          value={endDate ?? ''}
          onChange={(e) => onEndDateChange(e.target.value || null)}
        />
      </label>

      <label>
        Stage:
        <select
          value={selectedStage ?? ''}
          onChange={(e) => onStageChange(e.target.value || null)}
        >
          <option value="">All</option>
          {stages.map((stage) => (
            <option key={stage} value={stage}>
              {stage}
            </option>
          ))}
        </select>
      </label>

      <label>
        Show Dry Runs Only:
        <input
          type="checkbox"
          checked={dryRunOnly}
          onChange={(e) => onDryRunToggle(e.target.checked)}
        />
      </label>
    </div>
  );
};
