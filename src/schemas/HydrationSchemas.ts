// project-root/src/schemas/HydrationSchemas.ts

// src/schemas/HydrationSchemas.ts

import { z } from 'zod'

export const HydrationResponseSchema = z.object({
  contributorId: z.string(),
  rhythm: z.enum(['steady', 'surging', 'paused']),
  onboardingOverlay: z.boolean(),
  badges: z.array(z.string())
})

export type HydrationResponse = z.infer<typeof HydrationResponseSchema>
