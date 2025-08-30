// project-root/utils/validateFullManifest.ts
import { routeManifest } from '../manifests/subject.manifest';
export const validateFullManifest = () => {
    const errors = [];
    for (const [routeKey, config] of Object.entries(routeManifest)) {
        // Transition validation
        const transitions = config.transition;
        for (const role of ['visitor', 'user', 'developer']) {
            if (!transitions?.[role]) {
                errors.push(`Missing transition config for '${role}' in ${routeKey}`);
            }
        }
        // SEO validation
        const seo = config.seo;
        if (!seo?.title || !seo?.description || !Array.isArray(seo.keywords)) {
            errors.push(`Incomplete SEO metadata in ${routeKey}`);
        }
        // Access validation
        const access = config.access;
        for (const role of ['visitor', 'user', 'developer']) {
            if (typeof access?.[role] !== 'boolean') {
                errors.push(`Missing access flag for '${role}' in ${routeKey}`);
            }
        }
    }
    if (errors.length > 0) {
        console.warn('⚠️ Manifest validation failed:');
        errors.forEach(err => console.warn(`- ${err}`));
    }
    else {
        console.info('✅ Manifest validation passed.');
    }
};
