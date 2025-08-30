import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/persons/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const personsmanifest = manifestRegistry.subjects.get('persons');
export default function PersonsPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!personsmanifest)
        return _jsx("div", { children: "Persons content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: personsmanifest.title }), _jsx("p", { children: personsmanifest.description })] }));
}
