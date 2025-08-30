// project-root/scripts/generateRouteManifest.ts

import fs from 'fs'
import path from 'path'

type Role = 'visitor' | 'user' | 'developer'

const defaultTransition: Record<Role, any> = {
  visitor: { type: 'fade', duration: 300, direction: 'vertical', easing: 'ease-in-out' },
  user: { type: 'fade', duration: 400, direction: 'vertical', easing: 'ease-out' },
  developer: { type: 'fade', duration: 150, direction: 'vertical', easing: 'linear' },
}

const lifecycleHooks = {
  onEnter: `() => console.log('Entering route')`,
  onExit: `() => console.log('Exiting route')`,
}

const accessMatrix: Record<string, Partial<Record<Role, boolean>>> = {
  '/about': { visitor: true, user: true, developer: true },
  '/contact': { visitor: true, user: false, developer: true },
  '/faq': { visitor: true, user: true, developer: false },
}

const pagesDir = path.resolve(__dirname, '../pages')
const routes = fs.readdirSync(pagesDir)
  .filter(file => file.endsWith('.tsx') || file.endsWith('.ts'))
  .map(file => `/${file.replace(/\.(tsx|ts)$/, '')}`)

const generateManifestEntry = (route: string) => ({
  [route]: {
    transition: defaultTransition,
    lifecycle: lifecycleHooks,
    seo: {
      title: `${route.replace('/', '').toUpperCase()} | Mkomigbo`,
      description: `Auto-generated description for ${route}`,
      keywords: [route.replace('/', ''), 'mkomigbo'],
    },
    access: accessMatrix[route] || {
      visitor: true,
      user: true,
      developer: true,
    },
  },
})

const manifest = routes.reduce((acc, route) => {
  return { ...acc, ...generateManifestEntry(route) }
}, {})

const outputPath = path.resolve(__dirname, '../manifests/generated.manifest.ts')
const outputContent = `// Auto-generated manifest\n\nexport const routeManifest: Record<string, any> = ${JSON.stringify(manifest, null, 2)};\n`

fs.writeFileSync(outputPath, outputContent)

try {
  JSON.parse(JSON.stringify(manifest))
  console.log('✅ Manifest validated successfully')
  console.log(`📦 Manifest written to ${outputPath}`)
} catch (err) {
  console.error('❌ Manifest validation failed:', err)
}
