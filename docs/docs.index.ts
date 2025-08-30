// project-root/docs/docs.index.ts

export type DocEntry = { id: string; title: string; summary: string; href: string };

export const docsIndex: DocEntry[] = [
    { id: "tokens", title: "Theme Tokens", summary: "Colors, rhythm, and motion primitives.", href: "/docs/theme-tokens" },
    { id: "nav", title: "Navigation", summary: "Subject-aware links and active states.", href: "/docs/navigation" },
    { id: "overlays", title: "Overlays", summary: "Onboarding layers, a11y, and motion.", href: "/docs/overlays" },
    { id: "badges", title: "Badges", summary: "Tiers, milestones, and celebration hooks.", href: "/docs/badges" }
];
