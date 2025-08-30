// project-root/scripts/cli/flags/env.ts
import os from 'os';
import path from 'path';
export function showEnv(configPath) {
    console.log('🧠 Runtime Environment Info:');
    console.log(`OS: ${os.platform()} ${os.release()}`);
    console.log(`Node Version: ${process.version}`);
    console.log(`Current Directory: ${process.cwd()}`);
    if (configPath) {
        console.log(`Config Path: ${path.resolve(configPath)}`);
    }
}
