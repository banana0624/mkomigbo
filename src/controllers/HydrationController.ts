// project-root/src/controllers/HydrationController.ts

import { Request, Response } from 'express'
import { getHydrationPayload } from '../services/HydrationService.js'
import { HydrationResponseSchema } from '../schemas/HydrationSchemas.js'

export const hydratePlatform = async (req: Request, res: Response) => {
  try {
    const contributorId = req.params.id
    const payload = await getHydrationPayload(contributorId)

    const validation = HydrationResponseSchema.safeParse(payload)
    if (!validation.success) {
      return res.status(400).json({ error: 'Invalid hydration payload' })
    }

    return res.status(200).json(validation.data)
  } catch (error) {
    console.error('Hydration error:', error)
    return res.status(500).json({ error: 'Internal server error' })
  }
}
