// project-root/src/components/OnboardingOverlay.tsx

import { useEffect, useState } from 'react'
import './OnboardingOverlay.css'

type Props = {
  overlayStatus: 'initial' | 'guided' | 'complete'
}

export const OnboardingOverlay = ({ overlayStatus }: Props) => {
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    if (overlayStatus !== 'complete') {
      setVisible(true)
      const timer = setTimeout(() => setVisible(false), 3000)
      return () => clearTimeout(timer)
    }
  }, [overlayStatus])

  if (!visible) return null

  return (
    <div className={`onboarding-overlay status-${overlayStatus}`}>
      {overlayStatus === 'initial' && <p>Welcome aboard!</p>}
      {overlayStatus === 'guided' && <p>You're progressing beautifully.</p>}
    </div>
  )
}
