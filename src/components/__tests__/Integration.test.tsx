// project-root/src/components/__tests__/Integration.test.tsx

import { render, screen } from '@testing-library/react'
import { RhythmDashboard } from '../RhythmDashboard'
import { OnboardingOverlay } from '../OnboardingOverlay'
import { BackupImpactDashboard } from '../BackupImpactDashboard'
import { BackupSummaryOverlay } from '../BackupSummaryOverlay'
import '@testing-library/jest-dom'

describe('Integration: Contributor Components', () => {
  const contributors = [
    { id: 'c001', name: 'Amina', rhythmScore: 12, badgeState: 'newcomer' },
    { id: 'c002', name: 'Kwame', rhythmScore: 47, badgeState: 'momentum' },
    { id: 'c003', name: 'Zainab', rhythmScore: 89, badgeState: 'trailblazer' }
  ]

  const summary = {
    totalEntries: 3,
    lastBackup: '2025-08-22T17:48:00Z',
    sizeEstimate: '4.55 GB',
    entries: [],
    contributorImpact: {
      totalContributors: 3,
      recentJoins: 1,
      badgesIssued: 2
    }
  }

  it('renders rhythm dashboard with contributor names', () => {
    render(<RhythmDashboard contributors={contributors} />)
    expect(screen.getByText(/Amina/)).toBeInTheDocument()
    expect(screen.getByText(/Kwame/)).toBeInTheDocument()
    expect(screen.getByText(/Zainab/)).toBeInTheDocument()
  })

  it('renders onboarding overlay when status is guided', () => {
    render(<OnboardingOverlay overlayStatus="guided" />)
    expect(screen.getByText(/progressing beautifully/)).toBeInTheDocument()
  })

  it('renders backup impact dashboard with metrics', () => {
    render(<BackupImpactDashboard summary={summary} />)
    expect(screen.getByText(/Total Entries: 3/)).toBeInTheDocument()
    expect(screen.getByText(/Badges Issued: 2/)).toBeInTheDocument()
  })

  it('renders backup summary overlay with size and timestamp', () => {
    render(
      <BackupSummaryOverlay
        totalEntries={summary.totalEntries}
        sizeEstimate={summary.sizeEstimate}
        lastBackup={summary.lastBackup}
      />
    )
    expect(screen.getByText(/🗂 3 backups/)).toBeInTheDocument()
    expect(screen.getByText(/📦 4.55 GB total/)).toBeInTheDocument()
  })
})
