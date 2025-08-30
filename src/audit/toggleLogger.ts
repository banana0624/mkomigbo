// project-root/src/audit/toggleLogger.ts

import fs from 'fs';
import path from 'path';

const logFile = path.join(__dirname, 'toggle-audit.json');

export function logToggleEvent(mode: 'dry-run' | 'real') {
  const timestamp = new Date().toISOString();
  const entry = { timestamp, mode };

  const existing = fs.existsSync(logFile)
    ? JSON.parse(fs.readFileSync(logFile, 'utf-8'))
    : [];

  existing.push(entry);
  fs.writeFileSync(logFile, JSON.stringify(existing, null, 2));
}