#!/usr/bin/env ts-node

// project-root/scripts/cli/manifest.ts

import { execSync } from 'child_process';
import path from 'path';
import fs from 'fs';
import { retrySafe } from '../utils/runSafe';
import { cliFlags } from '../cli/cliFlags';

import { log } from '../utils/log';

const { force, dryRun, verbose } = cliFlags;

await log.timing('Generate manifest', async () => {
  if (dryRun) {
    log.dryRun('ts-node scripts/generateRouteManifest.ts');
  } else {
    execSync('ts-node scripts/generateRouteManifest.ts', { stdio: 'inherit' });
  }
}, verbose)();

await retrySafe('Validate manifest', async () => {
  const manifestPath = path.resolve(__dirname, '../../manifests/generated.manifest.json');
  if (!fs.existsSync(manifestPath)) {
    if (force) {
      log.warn('Manifest missing, continuing due to --force.');
      return;
    }
    throw new Error('Manifest JSON missing.');
  }
  log.verbose(`Manifest found at ${manifestPath}`, verbose);
});

await log.timing('Merge manifests', async () => {
  if (dryRun) {
    log.dryRun('ts-node scripts/mergeGeneratedManifest.ts');
  } else {
    execSync('ts-node scripts/mergeGeneratedManifest.ts', { stdio: 'inherit' });
  }
}, verbose)();

// Utility to conditionally execute shell commands
function maybeExec(command: string) {
  if (dryRun) {
    console.log(`[dry-run] Would execute: ${command}`);
  } else {
    execSync(command, { stdio: 'inherit' });
  }
}

// Step 1: Generate manifest
await retrySafe('Generating manifest', async () => {
  maybeExec('ts-node scripts/generateRouteManifest.ts');
});

// Step 2: Validate JSON exists
await retrySafe('Validating generated manifest', async () => {
  const generatedPath = path.resolve(__dirname, '../../manifests/generated.manifest.json');
  if (!fs.existsSync(generatedPath)) {
    if (force) {
      console.warn('⚠️ Manifest missing, but continuing due to --force flag.');
      return;
    }
    throw new Error('Manifest JSON file missing.');
  }
  if (verbose) {
    console.log(`[verbose] Found manifest at: ${generatedPath}`);
  }
});

// Step 3: Merge manifests
await retrySafe('Merging manifests', async () => {
  maybeExec('ts-node scripts/mergeGeneratedManifest.ts');
});