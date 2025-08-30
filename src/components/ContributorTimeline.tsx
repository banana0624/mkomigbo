// project-root/src/components/ContributorTimeline.tsx

type ContributorEvent = {
  timestamp: string
  contributorId: string
  badgeState: string
  rhythmScore: number
  overlayStatus: string
}

type Props = {
  events: ContributorEvent[]
}

export const ContributorTimeline = ({ events }: Props) => {
  return (
    <div className="contributor-timeline">
      <h2>Contributor Timeline</h2>
      <ul>
        {events.map(({ timestamp, contributorId, badgeState, rhythmScore, overlayStatus }) => (
          <li key={`${contributorId}-${timestamp}`} className={`timeline-entry badge-${badgeState}`}>
            <span>{new Date(timestamp).toLocaleDateString()}</span>
            <strong>{contributorId}</strong> – {badgeState}, Rhythm: {rhythmScore}, Overlay: {overlayStatus}
          </li>
        ))}
      </ul>
    </div>
  )
}
