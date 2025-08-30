#!/usr/bin/env ts-node

// project-root/scripts/cli/audit-hooks.ts

import fs from 'fs';
import path from 'path';
import chalk from 'chalk';

const args = process.argv.slice(2);
const dryRun = args.includes('--dry-run');
const verbose = args.includes('--verbose');
const trace = args.includes('--trace');

const projectRoot = path.resolve(__dirname, '../../');
const hooksDir = path.join(projectRoot, 'scripts/hooks');
const manifestsDir = path.join(projectRoot, 'manifests');

function log(msg: string, level: 'info' | 'warn' | 'trace' = 'info') {
  if (level === 'trace' && !trace) return;
  const prefix = level === 'warn' ? chalk.yellow('[warn]') : level === 'trace' ? chalk.gray('[trace]') : chalk.green('[info]');
  console.log(`${prefix} ${msg}`);
}

function getAllFiles(dir: string, ext: string, collected: string[] = []): string[] {
  if (!fs.existsSync(dir)) return collected;
  for (const entry of fs.readdirSync(dir)) {
    const fullPath = path.join(dir, entry);
    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      getAllFiles(fullPath, ext, collected);
    } else if (entry.endsWith(ext)) {
      collected.push(fullPath);
    }
  }
  return collected;
}

function searchUsage(filePath: string, keyword: string): boolean {
  const content = fs.readFileSync(filePath, 'utf-8');
  return content.includes(keyword);
}

function auditHooks() {
  const hookFiles = getAllFiles(hooksDir, '.ts');
  const allProjectFiles = getAllFiles(projectRoot, '.ts');

  const unusedHooks: string[] = [];

  for (const hookFile of hookFiles) {
    const hookName = path.basename(hookFile, '.ts');
    const used = allProjectFiles.some(file => searchUsage(file, hookName));
    if (!used) {
      unusedHooks.push(hookFile);
      log(`Unused hook: ${hookName}`, 'warn');
    } else if (verbose) {
      log(`Used hook: ${hookName}`, 'trace');
    }
  }

  return unusedHooks;
}

function auditManifests() {
  const manifestFiles = getAllFiles(manifestsDir, '.ts');
  const allProjectFiles = getAllFiles(projectRoot, '.ts');

  const unusedManifests: string[] = [];

  for (const manifestFile of manifestFiles) {
    const manifestName = path.basename(manifestFile, '.ts');
    const used = allProjectFiles.some(file => searchUsage(file, manifestName));
    if (!used) {
      unusedManifests.push(manifestFile);
      log(`Unused manifest: ${manifestName}`, 'warn');
    } else if (verbose) {
      log(`Used manifest: ${manifestName}`, 'trace');
    }
  }

  return unusedManifests;
}

function main() {
  log(`Starting audit${dryRun ? ' (dry-run)' : ''}...`);

  const unusedHooks = auditHooks();
  const unusedManifests = auditManifests();

  if (!dryRun) {
    const outputPath = path.join(projectRoot, 'logs/audit-unused.json');
    const output = {
      timestamp: new Date().toISOString(),
      unusedHooks,
      unusedManifests,
    };
    fs.mkdirSync(path.dirname(outputPath), { recursive: true });
    fs.writeFileSync(outputPath, JSON.stringify(output, null, 2));
    log(`Audit results written to ${outputPath}`);
  } else {
    log(`Dry-run complete. No files written.`);
  }
}

main();
