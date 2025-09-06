// project-root/types/telemetry.d.ts

export interface TelemetryEvent {
  id: string;
  timestamp: string;
  source: string;
  status: 'success' | 'failure' | 'pending';
}
