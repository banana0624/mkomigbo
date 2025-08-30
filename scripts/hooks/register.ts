// project-root/scripts/hooks/register.ts

import path from 'path'
import fs from 'fs'
import yaml from 'js-yaml'
import { HookContext } from './types'

type HookDeclaration = {
  module: string
  role: string
  action: string
}

type ManifestHooks = {
  [phase: string]: HookDeclaration[]
}

const HOOKS_DIR = path.resolve(process.cwd(), 'hooks')

function validateHookPath(actionPath: string): boolean {
  const resolved = path.resolve(process.cwd(), actionPath)
  return resolved.startsWith(HOOKS_DIR)
}

export async function registerHooksFromManifest(manifestPath: string) {
  const ext = path.extname(manifestPath)
  const raw = fs.readFileSync(manifestPath, 'utf-8')
  const manifest: ManifestHooks = ext === '.yaml' || ext === '.yml'
    ? yaml.load(raw) as ManifestHooks
    : JSON.parse(raw)

  for (const [phase, declarations] of Object.entries(manifest)) {
    for (const decl of declarations) {
      if (!validateHookPath(decl.action)) {
        console.warn(`⚠️ Hook action path "${decl.action}" is outside allowed directory: ${HOOKS_DIR}`)
        continue
      }

      const hookModule = await import(path.resolve(process.cwd(), decl.action))
      const hookFn = hookModule.default || hookModule.run

      if (typeof hookFn !== 'function') {
        console.warn(`⚠️ No valid function exported from ${decl.action}`)
        continue
      }

      const context: HookContext = {
        module: decl.module,
        role: decl.role,
        verbose: true,
        dryRun: false,
      }

      await hookFn(context)
      console.log(`✅ Executed hook for phase "${phase}" [${decl.module}:${decl.role}]`)
    }
  }
}