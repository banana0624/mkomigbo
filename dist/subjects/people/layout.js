import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/people/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const peoplemanifest = manifestRegistry.subjects.get('people');
export default function PeoplePage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!peoplemanifest)
        return _jsx("div", { children: "People content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: peoplemanifest.title }), _jsx("p", { children: peoplemanifest.description })] }));
}
