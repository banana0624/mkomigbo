// project-root/scripts/flagsAudit.ts

import fs from 'node:fs';
import path from 'node:path';

const FLAGS_PATH = path.resolve('.copilot/onboarding/flags.json');

function readFlags() {
  if (!fs.existsSync(FLAGS_PATH)) return null;
  return JSON.parse(fs.readFileSync(FLAGS_PATH, 'utf8'));
}

function auditFlags() {
  const flags = readFlags();
  if (!flags) {
    console.error('No flags found.');
    process.exitCode = 1;
    return;
  }

  const { jsExtensionCompliance } = flags.badges;
  const badgePulse = jsExtensionCompliance === 100;

  const extended = {
    ...flags,
    badgePulse,
    themeTokenAudit: 'pending',
    directoryClarityScore: 'pending',
  };

  fs.writeFileSync(FLAGS_PATH, JSON.stringify(extended, null, 2), 'utf8');
  console.log(`Flags audited. Badge pulse: ${badgePulse ? 'active' : 'inactive'}`);
}

auditFlags();

