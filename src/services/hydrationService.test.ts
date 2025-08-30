// project-root/src/services/hydrationService.test.ts

import { getHydrationPayload } from './hydrationService'
import { HydrationResponseSchema } from '../schemas/HydrationSchemas'

describe('getHydrationPayload', () => {
  it('should return a valid HydrationResponse', async () => {
    const payload = await getHydrationPayload('c001')
    const validation = HydrationResponseSchema.safeParse(payload)
    expect(validation.success).toBe(true)
  })

  it('should include contributorId in response', async () => {
    const id = 'c002'
    const payload = await getHydrationPayload(id)
    expect(payload.contributorId).toBe(id)
  })
})
