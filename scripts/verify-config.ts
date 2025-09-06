// project-root/scripts/verify-config.ts

import fs from 'node:fs';

export function verifyTsConfig() {
  const configExists = fs.existsSync('tsconfig.json');
  console.log(configExists ? '✅ tsconfig.json found' : '❌ tsconfig.json missing');
}
