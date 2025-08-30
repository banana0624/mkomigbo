import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/europe/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const europemanifest = manifestRegistry.subjects.get('europe');
export default function EuropePage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!europemanifest)
        return _jsx("div", { children: "Europe content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: europemanifest.title }), _jsx("p", { children: europemanifest.description })] }));
}
