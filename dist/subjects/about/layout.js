import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
// project-root/subjects/about/layout.tsx
import { useEffect } from 'react';
import { Helmet } from 'react-helmet';
import { manifestRegistry } from '../../core/registries/manifestRegistry';
import { lifecycleRegistry } from '../../core/registries/lifecycleRegistry';
const aboutmanifest = manifestRegistry.subjects.get('about');
export default function AboutPage() {
    useEffect(() => {
        lifecycleRegistry.onInit.forEach(hook => hook());
        return () => {
            lifecycleRegistry.onDestroy.forEach(hook => hook());
        };
    }, []);
    if (!aboutmanifest)
        return _jsx("div", { children: "About content not found." });
    const { title, description, meta } = aboutmanifest;
    return (_jsxs(_Fragment, { children: [_jsxs(Helmet, { children: [_jsx("title", { children: meta?.pageTitle || title }), _jsx("meta", { name: "description", content: meta?.description || description }), _jsx("meta", { name: "keywords", content: meta?.keywords?.join(', ') || '' }), _jsx("meta", { name: "subject", content: meta?.subject || title })] }), _jsxs("main", { children: [_jsx("h1", { children: title }), _jsx("p", { children: description })] })] }));
}
