// project-root/server/db/index.ts

export const db = {
  connect: () => console.log('🔗 Connected to database'),
  disconnect: () => console.log('❌ Disconnected from database')
};
