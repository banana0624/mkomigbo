// project-root/server/api/index.ts

export function getStatus() {
  return { status: 'ok', timestamp: new Date().toISOString() };
}
