// platform/bootstrap.ts

import { preloadCoreSubjects } from '../core/registries/coreSubjects';
import { manifestRegistry } from '../core/registries/manifestRegistry';

// Preload core subject manifests early
preloadCoreSubjects();

// Optionally register them into the manifest registry
// manifestRegistry.register(...);

// Continue with other lifecycle hooks or module initializations