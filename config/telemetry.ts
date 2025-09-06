// project-root/config/telemetry.ts

export function logEvent(event: string, data?: Record<string, unknown>) {
  console.log(`📡 Telemetry: ${event}`, data || {});
}
