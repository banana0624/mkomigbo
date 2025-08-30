// project-root/hook/useTransitionConfig.ts

import { routeManifest } from '../manifests/subject.manifest'

type Role = 'visitor' | 'user' | 'developer'

export const useTransitionConfig = (routeKey: string, role: Role) => {
  const config = routeManifest[routeKey]?.transition?.[role]

  return config || {
    type: 'fade',
    duration: 300,
    direction: 'vertical',
    easing: 'ease-in-out',
  }
}