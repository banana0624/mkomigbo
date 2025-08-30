import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/religion/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const religionmanifest = manifestRegistry.subjects.get('religion');
export default function ReligionPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!religionmanifest)
        return _jsx("div", { children: "Religion content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: religionmanifest.title }), _jsx("p", { children: religionmanifest.description })] }));
}
