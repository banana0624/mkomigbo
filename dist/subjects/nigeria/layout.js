import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/nigeria/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const nigeriamanifest = manifestRegistry.subjects.get('nigeria');
export default function NigeriaPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!nigeriamanifest)
        return _jsx("div", { children: "Nigeria content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: nigeriamanifest.title }), _jsx("p", { children: nigeriamanifest.description })] }));
}
