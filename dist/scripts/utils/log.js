// project-root/scripts/utils/log.ts
import fs from 'fs';
import path from 'path';
import chalk from 'chalk';
const logFilePath = path.resolve(__dirname, '../../logs/cli.log');
const lifecycleTracePath = path.resolve(__dirname, '../../logs/lifecycle.trace.log');
// Ensure log directory exists
fs.mkdirSync(path.dirname(logFilePath), { recursive: true });
function writeToFile(filePath, msg) {
    fs.appendFileSync(filePath, `${new Date().toISOString()} ${msg}\n`);
}
function format(level, msg) {
    return `[${level}] ${msg}`;
}
export const log = {
    info: (msg) => {
        const formatted = format('info', msg);
        console.log(chalk.blue(formatted));
        writeToFile(logFilePath, formatted);
    },
    warn: (msg) => {
        const formatted = format('warn', msg);
        console.warn(chalk.yellow(formatted));
        writeToFile(logFilePath, formatted);
    },
    error: (msg) => {
        const formatted = format('error', msg);
        console.error(chalk.red(formatted));
        writeToFile(logFilePath, formatted);
    },
    dryRun: (msg) => {
        const formatted = format('dry-run', msg);
        console.log(chalk.magenta(formatted));
        writeToFile(logFilePath, formatted);
    },
    verbose: (msg, enabled) => {
        if (enabled) {
            const formatted = format('verbose', msg);
            console.log(chalk.gray(formatted));
            writeToFile(logFilePath, formatted);
        }
    },
    timing: (label, fn, enabled) => {
        return async () => {
            if (!enabled)
                return await fn();
            console.time(`[timing] ${label}`);
            await fn();
            console.timeEnd(`[timing] ${label}`);
            writeToFile(logFilePath, `[timing] ${label} completed`);
        };
    },
    trace: (hook, context) => {
        const traceMsg = `[trace] ${hook} → ${JSON.stringify(context)}`;
        writeToFile(lifecycleTracePath, traceMsg);
    }
};
