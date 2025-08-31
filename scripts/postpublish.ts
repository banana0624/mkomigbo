// project-root/scripts/postpublish.ts

import fs from 'node:fs';
import path from 'node:path';

const ROOT = process.cwd();
const COPILOT_DIR = path.join(ROOT, '.copilot');
const ONBOARD_DIR = path.join(COPILOT_DIR, 'onboarding');
const CELEB_DIR = path.join(COPILOT_DIR, 'celebrations');
const FLAGS_PATH = path.join(ONBOARD_DIR, 'flags.json');
const LOG_PATH = path.join(CELEB_DIR, 'log.json');

type Flags = {
  showOnboardingOverlay: boolean;
  badgePulse?: boolean;
  badges: { jsExtensionCompliance: number };
  tips: string[];
  themeTokenAudit?: 'pending' | 'complete';
  directoryClarityScore?: 'low' | 'moderate' | 'excellent';
};

function ensureDir(p: string) {
  if (!fs.existsSync(p)) fs.mkdirSync(p, { recursive: true });
}

function readFlags(): Flags | null {
  try {
    const raw = fs.readFileSync(FLAGS_PATH, 'utf8');
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function writeFlags(flags: Flags) {
  ensureDir(ONBOARD_DIR);
  fs.writeFileSync(FLAGS_PATH, JSON.stringify(flags, null, 2), 'utf8');
}

function appendCelebration(entry: Record<string, unknown>) {
  ensureDir(CELEB_DIR);
  const list = fs.existsSync(LOG_PATH) ? JSON.parse(fs.readFileSync(LOG_PATH, 'utf8')) : [];
  list.push(entry);
  fs.writeFileSync(LOG_PATH, JSON.stringify(list, null, 2), 'utf8');
}

function celebrate() {
  const prev = readFlags();
  const next: Flags = {
    showOnboardingOverlay: true,
    badgePulse: true,
    badges: { jsExtensionCompliance: prev?.badges?.jsExtensionCompliance ?? 100 },
    tips: ['We’re live! Explore the launch post and contributor guide.'],
    themeTokenAudit: 'complete',
    directoryClarityScore: 'excellent',
  };
  writeFlags(next);

  appendCelebration({
    timestamp: new Date().toISOString(),
    event: 'postpublish:celebration',
    compliance: next.badges.jsExtensionCompliance,
  });

  console.log('Postpublish celebration flags written to .copilot/onboarding/flags.json');
  console.log('Celebration log appended at .copilot/celebrations/log.json');
}

celebrate();
