#!/usr/bin/env ts-node
// project-root/scripts/cli/cli.ts
import { handleRestore } from './flags/restore';
import { handleTrashExpiry } from './flags/trashExpiry';
import { handlePurge } from './flags/purge';
import { handleHelp as showHelp } from './flags/help';
import { showEnv } from './flags/env';
showEnv('--config.json');
function main() {
    const args = process.argv.slice(2);
    if (args.includes('--help'))
        return showHelp();
    if (args.some(arg => arg.startsWith('--restore=')))
        return handleRestore(args);
    if (args.some(arg => arg.startsWith('--trash-expiry=')))
        return handleTrashExpiry(args);
    if (args.includes('--purge'))
        return handlePurge(args);
    console.log('❓ Unknown command. Use --help to see available options.');
}
main();
