// project-root/scripts/audit/checkManifestRegistryImports.ts

// scripts/audit/checkManifestRegistryImports.ts

import fs from 'fs';
import path from 'path';

const rootDir = path.resolve(__dirname, '../../');
const targetPattern = /['"]\.{1,2}\/.*manifestregistry['"]/i;

function scanFile(filePath: string) {
  const content = fs.readFileSync(filePath, 'utf-8');
  if (targetPattern.test(content)) {
    console.log(`❌ Incorrect casing in: ${filePath}`);
  }
}

function walk(dir: string) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      walk(fullPath);
    } else if (entry.isFile() && fullPath.endsWith('.ts')) {
      scanFile(fullPath);
    }
  }
}

console.log('🔍 Auditing manifestRegistry import casing...');
walk(rootDir);
console.log('✅ Audit complete.');
