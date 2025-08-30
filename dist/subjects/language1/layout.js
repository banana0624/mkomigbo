import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/language1/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const language1manifest = manifestRegistry.subjects.get('language1');
export default function Language1Page() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!language1manifest)
        return _jsx("div", { children: "Language1 content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: language1manifest.title }), _jsx("p", { children: language1manifest.description })] }));
}
