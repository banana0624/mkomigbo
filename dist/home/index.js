import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
// project-root/home/index.tsx
import React from 'react';
import router from './router';
const HomePage = () => {
    const [currentPath, setCurrentPath] = React.useState('/subjects/subject-about');
    const [ResolvedComponent, setResolvedComponent] = React.useState(null);
    React.useEffect(() => {
        const resolveRoute = () => {
            const component = router.resolve(currentPath);
            setResolvedComponent(component ?? null);
        };
        resolveRoute();
    }, [currentPath]);
    return (_jsxs("div", { className: "home-page", children: [_jsxs("nav", { children: [_jsx("button", { onClick: () => setCurrentPath('/subjects/subject-about'), children: "About" }), _jsx("button", { onClick: () => setCurrentPath('/subjects/subject-history'), children: "History" })] }), _jsx("section", { children: ResolvedComponent })] }));
};
export default HomePage;
