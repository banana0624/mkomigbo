import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/culture/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const cultureManifest = manifestRegistry.subjects.get('culture');
export default function CulturePage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!cultureManifest)
        return _jsx("div", { children: "Culture content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: cultureManifest.title }), _jsx("p", { children: cultureManifest.description })] }));
}
