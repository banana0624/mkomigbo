// src/api/controllers/getContributors.ts

import { GetContributorsResponse } from '../../types/api/responses'
import { mockContributors } from '../../backups/mockData'

export const getContributors = (): GetContributorsResponse => {
  return mockContributors
}
