// project-root/src/cli.ts
import path from 'path';
import fs from 'fs';
const backups = JSON.parse(fs.readFileSync(path.join(__dirname, '../data/backups.json'), 'utf8'));
console.log('Loaded backups:', backups);
