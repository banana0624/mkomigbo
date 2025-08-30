// project-root/home/index.tsx

import React from 'react'
import router from './router'

const HomePage: React.FC = () => {
  const [currentPath, setCurrentPath] = React.useState('/subjects/subject-about')
  const [ResolvedComponent, setResolvedComponent] = React.useState<React.ReactElement | null>(null)

  React.useEffect(() => {
    const resolveRoute = () => {
      const component = router.resolve(currentPath)
      setResolvedComponent(component ?? null)
    }
    resolveRoute()
  }, [currentPath])

  return (
    <div className="home-page">
      <nav>
        <button onClick={() => setCurrentPath('/subjects/subject-about')}>About</button>
        <button onClick={() => setCurrentPath('/subjects/subject-history')}>History</button>
      </nav>
      <section>{ResolvedComponent}</section>
    </div>
  )
}

export default HomePage