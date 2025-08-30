// project-root/scripts/utils/log.ts

import fs from 'fs';
import path from 'path';
import chalk from 'chalk';

const logFilePath = path.resolve(__dirname, '../../logs/cli.log');
const lifecycleTracePath = path.resolve(__dirname, '../../logs/lifecycle.trace.log');

// Ensure log directory exists
fs.mkdirSync(path.dirname(logFilePath), { recursive: true });

function writeToFile(filePath: string, msg: string) {
  fs.appendFileSync(filePath, `${new Date().toISOString()} ${msg}\n`);
}

function format(level: string, msg: string) {
  return `[${level}] ${msg}`;
}

export const log = {
  info: (msg: string) => {
    const formatted = format('info', msg);
    console.log(chalk.blue(formatted));
    writeToFile(logFilePath, formatted);
  },
  warn: (msg: string) => {
    const formatted = format('warn', msg);
    console.warn(chalk.yellow(formatted));
    writeToFile(logFilePath, formatted);
  },
  error: (msg: string) => {
    const formatted = format('error', msg);
    console.error(chalk.red(formatted));
    writeToFile(logFilePath, formatted);
  },
  dryRun: (msg: string) => {
    const formatted = format('dry-run', msg);
    console.log(chalk.magenta(formatted));
    writeToFile(logFilePath, formatted);
  },
  verbose: (msg: string, enabled: boolean) => {
    if (enabled) {
      const formatted = format('verbose', msg);
      console.log(chalk.gray(formatted));
      writeToFile(logFilePath, formatted);
    }
  },
  timing: (label: string, fn: () => Promise<void>, enabled: boolean) => {
    return async () => {
      if (!enabled) return await fn();
      console.time(`[timing] ${label}`);
      await fn();
      console.timeEnd(`[timing] ${label}`);
      writeToFile(logFilePath, `[timing] ${label} completed`);
    };
  },
  trace: (hook: string, context: Record<string, any>) => {
    const traceMsg = `[trace] ${hook} → ${JSON.stringify(context)}`;
    writeToFile(lifecycleTracePath, traceMsg);
  }
};
