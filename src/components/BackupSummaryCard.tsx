// src/components/BackupSummaryCard.tsx

import React from 'react'
import { BackupSummary } from '../backups/types'

interface Props {
  summary: BackupSummary
}

export const BackupSummaryCard: React.FC<Props> = ({ summary }) => (
  <div className="summary-card">
    <h2>Backup Summary</h2>
    <p>Total Entries: {summary.totalEntries}</p>
    <p>Last Backup: {new Date(summary.lastBackup).toLocaleString()}</p>
    <p>Size Estimate: {summary.sizeEstimate}</p>
    <h3>Contributor Impact</h3>
    <ul>
      <li>Total Contributors: {summary.contributorImpact.totalContributors}</li>
      <li>Recent Joins: {summary.contributorImpact.recentJoins}</li>
      <li>Badges Issued: {summary.contributorImpact.badgesIssued}</li>
    </ul>
  </div>
)
