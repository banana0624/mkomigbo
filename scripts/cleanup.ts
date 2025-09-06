// project-root/scripts/cleanup.ts

import fs from 'node:fs';
import path from 'node:path';

export function cleanupRedundantFiles(dir: string) {
  const files = fs.readdirSync(dir);
  files.forEach((file) => {
    if (file.endsWith('.tmp') || file.endsWith('.bak')) {
      fs.unlinkSync(path.join(dir, file));
      console.log(`🧹 Removed: ${file}`);
    }
  });
}
