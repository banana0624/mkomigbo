// project-root/src/components/__tests__/AuditOverlay.test.tsx

import { render, screen } from '@testing-library/react'
import { AuditOverlay } from '../AuditOverlay'
import '@testing-library/jest-dom'

const mockProps = {
  contributorId: 'c003',
  rhythm: 'surging',
  overlayStatus: 'complete',
  badgeState: 'trailblazer'
}

describe('AuditOverlay', () => {
  it('renders contributor ID and badge', () => {
    render(<AuditOverlay {...mockProps} />)
    expect(screen.getByText(/c003/)).toBeInTheDocument()
    expect(screen.getByText(/trailblazer/)).toBeInTheDocument()
  })

  it('applies rhythm class', () => {
    render(<AuditOverlay {...mockProps} />)
    const container = screen.getByTestId('audit-overlay')
    expect(container.className).toContain('rhythm-surging')
  })
})
