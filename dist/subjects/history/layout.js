import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/subjects/history/layout.tsx
import { useEffect } from 'react';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const historyManifest = manifestRegistry.subjects.get('history');
export default function HistoryPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!historyManifest)
        return _jsx("div", { children: "History content not found." });
    return (_jsxs("main", { children: [_jsx("h1", { children: historyManifest.title }), _jsx("p", { children: historyManifest.description })] }));
}
