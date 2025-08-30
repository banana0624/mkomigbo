// project-root/scripts/dev/audit-exports.ts

import * as Navigation from '../utils/navigation';
import * as Platform from '../utils/platform';

function print(label: string, obj: object) {
  const keys = Object.keys(obj).sort();
  console.log(`${label}:`, keys.join(', ') || '(none)');
  return keys;
}

const nav = print('Navigation exports', Navigation);
const plat = print('Platform exports', Platform);

const overlap = nav.filter(k => plat.includes(k));
console.log('Overlapping exports:', overlap.join(', ') || '(none)');

if (overlap.length) {
  process.exitCode = 1;
}


