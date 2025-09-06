// project-root/domains/analytics/index.ts

export function trackEvent(event: string, metadata?: Record<string, unknown>) {
  console.log(`📊 Analytics Event: ${event}`, metadata || {});
}
