// project-root/lib/router.ts

// project-root/lib/router.ts
import React from 'react'

type RouteHandler = () => React.ReactElement

class Router {
  private routes: Record<string, RouteHandler> = {}

  register(path: string, handler: RouteHandler) {
    this.routes[path] = handler
  }

  init() {
    console.log('Router initialized with routes:', Object.keys(this.routes))
  }

  resolve(path: string): React.ReactElement | null {
    const handler = this.routes[path]
    return handler ? handler() : null
  }
}

export const createRouter = () => new Router()