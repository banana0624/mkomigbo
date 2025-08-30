// project-root/lib/router.ts
class Router {
    routes = {};
    register(path, handler) {
        this.routes[path] = handler;
    }
    init() {
        console.log('Router initialized with routes:', Object.keys(this.routes));
    }
    resolve(path) {
        const handler = this.routes[path];
        return handler ? handler() : null;
    }
}
export const createRouter = () => new Router();
