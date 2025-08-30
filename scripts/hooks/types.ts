// project-root/scripts/hooks/types.ts

export type HookContext = {
  module: string
  role: string
  dryRun?: boolean
  verbose?: boolean
  [key: string]: any // Allows extensibility for future flags or metadata
}