import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/language2/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const language2manifest = manifestRegistry.subjects.get('language2');
export default function language2Page() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!language2manifest)
        return _jsx("div", { children: "Language2 content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: language2manifest.title }), _jsx("p", { children: language2manifest.description })] }));
}
