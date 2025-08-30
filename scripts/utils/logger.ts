// project-root/scripts/utils/logger.ts

export const logInfo = (msg: string, context?: string): void => {
  console.info(`[info]${context ? ` [${context}]` : ''} ${msg}`);
};

export const logWarn = (msg: string, context?: string): void => {
  console.warn(`[warn]${context ? ` [${context}]` : ''} ${msg}`);
};

/** Logs a string message or an Error object with context. */
export function logError(error: string | Error | unknown, context?: string): void {
  const prefix = `[error]${context ? ` [${context}]` : ''}`;
  if (typeof error === 'string') {
    console.error(`${prefix} ${error}`);
  } else if (error instanceof Error) {
    console.error(`${prefix} ${error.message}\n${error.stack}`);
  } else {
    console.error(`${prefix} ${JSON.stringify(error)}`);
  }
}

/** Structured, contributor-safe logging for audit events. */
export function logEvent(event: string, data?: unknown): void {
  const payload = data ? ` ${JSON.stringify(data)}` : '';
  console.log(`[event] [${event}]${payload}`);
}
