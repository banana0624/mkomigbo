// project-root/utils/validateTransitionManifest.ts
import { routeManifest } from '../manifests/subject.manifest';
const requiredRoles = ['visitor', 'user', 'developer'];
export const validateTransitionManifest = () => {
    const errors = [];
    for (const [routeKey, config] of Object.entries(routeManifest)) {
        const transitions = config.transition;
        if (!transitions || typeof transitions !== 'object') {
            errors.push(`Missing 'transition' object for route: ${routeKey}`);
            continue;
        }
        for (const role of requiredRoles) {
            if (!transitions[role]) {
                errors.push(`Missing transition config for role '${role}' in route: ${routeKey}`);
            }
        }
    }
    if (errors.length > 0) {
        console.warn('⚠️ Transition manifest validation failed:');
        errors.forEach(err => console.warn(`- ${err}`));
    }
    else {
        console.info('✅ Transition manifest validation passed.');
    }
};
