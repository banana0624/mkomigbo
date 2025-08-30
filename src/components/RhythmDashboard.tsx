// project-root/src/components/RhythmDashboard.tsx

import './RhythmDashboard.css'

type Contributor = {
  id: string
  name: string
  rhythmScore: number
  badgeState: string
}

type Props = {
  contributors: Contributor[]
}

export const RhythmDashboard = ({ contributors }: Props) => {
  return (
    <div className="rhythm-dashboard">
      <h2>Contributor Rhythm Scores</h2>
      <ul>
        {contributors.map(({ id, name, rhythmScore, badgeState }) => (
          <li key={id} className={`rhythm-bar badge-${badgeState}`}>
            <span>{name}</span>
            <div className="score-bar">
              <div
                className="score-fill"
                style={{ width: `${rhythmScore}%` }}
              />
              <span className="score-label">{rhythmScore}</span>
            </div>
          </li>
        ))}
      </ul>
    </div>
  )
}
