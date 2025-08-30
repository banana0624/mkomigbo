// project-root/src/components/ContributorHydration.tsx

import { HydrationResponse } from '../schemas/HydrationSchemas'

type Props = HydrationResponse

export const ContributorHydration = ({
  contributorId,
  rhythm,
  onboardingOverlay,
  badges
}: Props) => {
  return (
    <div className={`hydration-container rhythm-${rhythm}`}>
      {onboardingOverlay && <div className="overlay">Welcome!</div>}
      <h2>Contributor: {contributorId}</h2>
      <ul>
        {badges.map(badge => (
          <li key={badge}>{badge}</li>
        ))}
      </ul>
    </div>
  )
}
