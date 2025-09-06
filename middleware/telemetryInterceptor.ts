// project-root/middleware/telemetryInterceptor.ts

export function telemetryInterceptor(event: string) {
  console.log(`📡 Intercepted telemetry event: ${event}`);
}
