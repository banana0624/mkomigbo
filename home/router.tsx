// project-root/home/router.ts

import { createRouter } from '../lib/router'
import manifest from '../manifests/root.json'
import React from 'react'

const getCurrentUserRole = (): string => {
  return 'admin' // mock role
}

const router = createRouter()

manifest.modules.forEach(module => {
  router.register(`/subjects/${module.id}`, () => {
    const DynamicRoute: React.FC = () => {
      const [Layout, setLayout] = React.useState<React.FC<any> | null>(null)
      const [Module, setModule] = React.useState<any>(null)
      const userRole = getCurrentUserRole()

      if (!module.roles?.includes(userRole)) {
        return <p>Access Denied</p>
      }

      React.useEffect(() => {
        const load = async () => {
          const layoutMod = await import(/* @vite-ignore */ module.layout)
          const moduleMod = await import(/* @vite-ignore */ module.path)
          setLayout(() => layoutMod.default)
          setModule(() => moduleMod)
        }
        load()
      }, [])

      if (!Layout || !Module) return <p>Loading...</p>

      return (
        <Layout title={Module.title}>
          <p>{Module.title} content goes here.</p>
        </Layout>
      )
    }

    return <DynamicRoute />
  })
})

export default router