import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/uk/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const ukmanifest = manifestRegistry.subjects.get('uk');
export default function UKPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!ukmanifest)
        return _jsx("div", { children: "UK content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: ukmanifest.title }), _jsx("p", { children: ukmanifest.description })] }));
}
