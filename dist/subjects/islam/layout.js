import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/islam/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const islamManifest = manifestRegistry.subjects.get('islam');
export default function IslamPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!islamManifest)
        return _jsx("div", { children: "Islam content not" });
    return (_jsxs("main", { children: [_jsx("h1", { children: islamManifest.title }), _jsx("p", { children: islamManifest.description })] }));
}
