// project-root/src/components/BackupImpactDashboard.tsx

import { BackupSummary } from '../backups/types'
import './BackupImpactDashboard.css'

type Props = {
  summary: BackupSummary
}

export const BackupImpactDashboard = ({ summary }: Props) => {
  return (
    <div className="backup-impact-dashboard">
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
}
