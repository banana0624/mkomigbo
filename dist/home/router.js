import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/home/router.ts
import { createRouter } from '../lib/router';
import manifest from '../manifests/root.json';
import React from 'react';
const getCurrentUserRole = () => {
    return 'admin'; // mock role
};
const router = createRouter();
manifest.modules.forEach(module => {
    router.register(`/subjects/${module.id}`, () => {
        const DynamicRoute = () => {
            const [Layout, setLayout] = React.useState(null);
            const [Module, setModule] = React.useState(null);
            const userRole = getCurrentUserRole();
            if (!module.roles?.includes(userRole)) {
                return _jsx("p", { children: "Access Denied" });
            }
            React.useEffect(() => {
                const load = async () => {
                    const layoutMod = await import(/* @vite-ignore */ module.layout);
                    const moduleMod = await import(/* @vite-ignore */ module.path);
                    setLayout(() => layoutMod.default);
                    setModule(() => moduleMod);
                };
                load();
            }, []);
            if (!Layout || !Module)
                return _jsx("p", { children: "Loading..." });
            return (_jsx(Layout, { title: Module.title, children: _jsxs("p", { children: [Module.title, " content goes here."] }) }));
        };
        return _jsx(DynamicRoute, {});
    });
});
export default router;
