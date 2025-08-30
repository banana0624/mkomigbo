import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/ipob/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const ipobmanifest = manifestRegistry.subjects.get('ipob');
export default function IPOBPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!ipobmanifest)
        return _jsx("div", { children: "IPOB content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: ipobmanifest.title }), _jsx("p", { children: ipobmanifest.description })] }));
}
