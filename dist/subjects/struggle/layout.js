import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/spirituality/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const strugglemanifest = manifestRegistry.subjects.get('struggle');
export default function StrugglePage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!strugglemanifest)
        return _jsx("div", { children: "Struggle content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: strugglemanifest.title }), _jsx("p", { children: strugglemanifest.description })] }));
}
