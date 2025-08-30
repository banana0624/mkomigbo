// project-root/hook/useTransitionConfig.ts
import { routeManifest } from '../manifests/subject.manifest';
export const useTransitionConfig = (routeKey, role) => {
    const config = routeManifest[routeKey]?.transition?.[role];
    return config || {
        type: 'fade',
        duration: 300,
        direction: 'vertical',
        easing: 'ease-in-out',
    };
};
