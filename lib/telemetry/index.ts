// project-root/lib/telemetry/index.ts

export function logTelemetry(event: string, data?: Record<string, unknown>) {
  console.log(`📡 Telemetry: ${event}`, data || {});
}
