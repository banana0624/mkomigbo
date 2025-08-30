// project-root/src/components/AuditOverlay.tsx

// src/components/AuditOverlay.tsx

type Props = {
  contributorId: string
  rhythm: string
  overlayStatus: string
  badgeState: string
}

export const AuditOverlay = ({
  contributorId,
  rhythm,
  overlayStatus,
  badgeState
}: Props) => {
  return (
    <div data-testid="audit-overlay" className={`audit-overlay rhythm-${rhythm}`}>
      <h3>{contributorId}</h3>
      <p>{badgeState}</p>
    </div>
  )
}
