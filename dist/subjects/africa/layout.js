import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/africa/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const africamanifest = manifestRegistry.subjects.get('africa');
export default function AfricaPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!africamanifest)
        return _jsx("div", { children: "Africa content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: africamanifest.title }), _jsx("p", { children: africamanifest.description })] }));
}
