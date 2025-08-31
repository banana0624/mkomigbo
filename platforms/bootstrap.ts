// project-root/platform/bootstrap.ts

import { preloadCoreSubjects } from '../core/registries/coreSubjects.js'
import { manifestRegistry } from '../core/registries/manifestRegistry.js'

// Preload core subject manifests early
preloadCoreSubjects()

// Register them into the manifest registry
manifestRegistry.register('core', {
  subjects: ['about', 'culture', 'history', 'nigeria'], // example subjects
  timestamp: Date.now(),
})

// Continue with other lifecycle hooks or module initializations
