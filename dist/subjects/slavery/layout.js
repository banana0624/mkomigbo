import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/slavery/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const slaverymanifest = manifestRegistry.subjects.get('slavery');
export default function SlaveryPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!slaverymanifest)
        return _jsx("div", { children: "Slavery content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: slaverymanifest.title }), _jsx("p", { children: slaverymanifest.description })] }));
}
