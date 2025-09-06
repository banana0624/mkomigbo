// project-riit/lib/logger.ts

export function logger(message: string, context?: string) {
  console.log(`[LOG] ${context || 'system'} → ${message}`);
}
