// project-root/cli/index.ts

import { runInit } from './commands/init';
import { runTrace } from './commands/trace';

const command = process.argv[2];

switch (command) {
  case 'init':
    runInit();
    break;
  case 'trace':
    runTrace();
    break;
  default:
    console.log('Unknown command. Try: init | trace');
}
