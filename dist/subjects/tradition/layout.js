import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/tradition/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const traditionmanifest = manifestRegistry.subjects.get('tradition');
export default function TraditionPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!traditionmanifest)
        return _jsx("div", { children: "Tradition content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: traditionmanifest.title }), _jsx("p", { children: traditionmanifest.description })] }));
}
