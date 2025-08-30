// project-root/components/RouteTransition.tsx

import React from 'react'
import { CSSTransition, TransitionGroup } from 'react-transition-group'
import { useTransitionConfig } from '../hooks/useTransitionConfig'
import './RouteTransition.css'

type Role = 'visitor' | 'user' | 'developer'

type Props = {
  children: React.ReactElement | null
  key: string
  role: Role
}

const RouteTransition: React.FC<Props> = ({ children, key, role }) => {
  const { type, duration, direction, easing } = useTransitionConfig(key, role)
  const seoClass = `transition-${key.replace('/', '-')}`

  return (
    <TransitionGroup>
      <CSSTransition key={key} timeout={duration} classNames={type}>
        <div
          className={`route-transition ${direction} ${seoClass}`}
          style={{ transitionTimingFunction: easing }}
        >
          {children}
        </div>
      </CSSTransition>
    </TransitionGroup>
  )
}

export default RouteTransition