// project-root/scripts/audit-imports.ts

import fs from 'node:fs';
import path from 'node:path';

const projectRoot = path.resolve('.');
const traceLog = path.resolve('scripts/trace.log');

function scanImports(dir: string) {
  const files = fs.readdirSync(dir, { withFileTypes: true });

  for (const file of files) {
    const fullPath = path.join(dir, file.name);

    if (file.isDirectory()) {
      scanImports(fullPath);
    } else if (file.name.endsWith('.ts') || file.name.endsWith('.tsx')) {
      const content = fs.readFileSync(fullPath, 'utf-8');
      const matches = content.match(/import\s.+?from\s['"].+?['"]/g) || [];

      if (matches.length > 0) {
        fs.appendFileSync(traceLog, `📦 ${file.name} → ${matches.length} imports\n`);
      }
    }
  }
}

scanImports(projectRoot);
console.log('✅ Import audit complete. See scripts/trace.log');
