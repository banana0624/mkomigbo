// project-root/scripts/cli/flags/congig.ts

import fs from 'fs';
import { CLIConfig } from '../../types/config';

export function loadConfig(path: string): CLIConfig {
  if (!fs.existsSync(path)) {
    throw new Error(`Config file not found at ${path}`);
  }
  return JSON.parse(fs.readFileSync(path, 'utf-8'));
}