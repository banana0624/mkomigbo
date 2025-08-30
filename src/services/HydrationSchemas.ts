// src/services/HydrationSchemas.ts

export interface HydrationResponse {
  contributorId: string
  rhythm: 'steady' | 'sporadic' | 'paused'
  onboardingOverlay: boolean
  badges: string[]
}
