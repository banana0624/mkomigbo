// project-root/prisma/seed.ts

import { PrismaClient } from '@prisma/client'
const prisma = new PrismaClient()

async function main() {
  await prisma.contributor.createMany({
    data: [
      { id: 'c001', name: 'Amina', badgeState: 'newcomer', overlayStatus: 'initial', rhythmScore: 12 },
      { id: 'c002', name: 'Kwame', badgeState: 'momentum', overlayStatus: 'guided', rhythmScore: 47 },
      { id: 'c003', name: 'Zainab', badgeState: 'trailblazer', overlayStatus: 'complete', rhythmScore: 89 },
    ],
  })

  await prisma.backupEntry.createMany({
    data: [
      { id: 'b001', contributorId: 'c001', size: '1.2 GB', timestamp: new Date('2025-08-20T14:32:00Z') },
      { id: 'b002', contributorId: 'c002', size: '850 MB', timestamp: new Date('2025-08-21T09:15:00Z') },
      { id: 'b003', contributorId: 'c003', size: '2.5 GB', timestamp: new Date('2025-08-22T17:48:00Z') },
    ],
  })
}

main().finally(() => prisma.$disconnect())
