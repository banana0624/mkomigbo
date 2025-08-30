// project-roor/src/backups/backupUtils.ts

export const parseSize = (size: string): number => {
  const [value, unit] = size.split(' ')
  const multiplier = unit === 'GB' ? 1024 : 1
  return parseFloat(value) * multiplier
}
