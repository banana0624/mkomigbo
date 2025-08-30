// project-root/scripts/hooks/sampleHook.ts

import { registerHook } from '../registry/traceHooks';

registerHook('onInit', async (ctx) => {
  console.log(`Initializing ${ctx.module} for role ${ctx.role}`);
});