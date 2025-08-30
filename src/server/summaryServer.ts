// project-root/src/server/summaryServer.ts

import express, { Request, Response } from 'express';
import { summarizeBackups } from '../backups/summarizeBackups';
import { summarizeBackupsDryRun } from '../backups/summarizeDryRun';
import { logToggleEvent } from '../audit/toggleLogger';

import fs from 'fs';           // ✅ Add this
import path from 'path';       // ✅ And this

const app = express();
const port = 3001;

app.get('/summary', async (req: Request, res: Response) => {
  const dryRun = req.query.dryRun === 'true';
  logToggleEvent(dryRun ? 'dry-run' : 'real');
  const summary = dryRun
    ? await summarizeBackupsDryRun()
    : await summarizeBackups();

  res.json(summary);
});

app.get('/audit', (req: Request, res: Response) => {
  const logPath = path.join(__dirname, '../audit/toggle-audit.json');
  const data = fs.existsSync(logPath)
    ? JSON.parse(fs.readFileSync(logPath, 'utf-8'))
    : [];
  res.json(data);
});

app.listen(port, () => {
  console.log(`Summary server running at http://localhost:${port}/summary`);
});
