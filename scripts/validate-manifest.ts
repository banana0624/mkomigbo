#!/usr/bin/env ts-node

// project-root/scripts/validate-manifest.ts

import { validateTransitionManifest } from '../utils/validateTransitionManifest'

console.log('🔍 Validating transition manifest...')
validateTransitionManifest()

import { validateFullManifest } from '../utils/validateFullManifest'

console.log('🔍 Validating full manifest...')
validateFullManifest()