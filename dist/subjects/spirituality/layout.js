import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/spirituality/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const spiritualitymanifest = manifestRegistry.subjects.get('spirituality');
export default function SpiritualityPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!spiritualitymanifest)
        return _jsx("div", { children: " Spirituality content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: spiritualitymanifest.title }), _jsx("p", { children: spiritualitymanifest.description })] }));
}
