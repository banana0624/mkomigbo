// project-root/src/components/BackupSummaryOverlay.tsx

import './BackupSummaryOverlay.css'

type Props = {
  totalEntries: number
  sizeEstimate: string
  lastBackup: string
}

export const BackupSummaryOverlay = ({ totalEntries, sizeEstimate, lastBackup }: Props) => {
  return (
    <div className="backup-summary-overlay">
      <p>🗂 {totalEntries} backups</p>
      <p>📦 {sizeEstimate} total</p>
      <p>🕒 Last: {new Date(lastBackup).toLocaleString()}</p>
    </div>
  )
}
