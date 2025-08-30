// project-root/src/utils/logger.ts

export const logger = {
  info: (msg: string) => {
    const timestamp = new Date().toISOString();
    console.log(`[INFO] ${timestamp} ${msg}`);
  },
};