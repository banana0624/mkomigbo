// project-root/scripts/cli/validateMergedManifest.ts
import fs from 'fs';
import path from 'path';
const mergedPath = path.resolve(__dirname, '../../manifests/merged.manifest.ts');
const content = fs.readFileSync(mergedPath, 'utf-8');
// ✅ SEO Checks
const seoTags = ['title', 'description', 'canonical'];
const missingSEO = seoTags.filter(tag => !content.includes(tag));
// ✅ Role Coverage
const roles = ['visitor', 'user', 'contributor', 'admin'];
const missingRoles = roles.filter(role => !content.includes(role));
if (missingSEO.length || missingRoles.length) {
    console.error('❌ Manifest validation failed:');
    if (missingSEO.length)
        console.error('Missing SEO tags:', missingSEO);
    if (missingRoles.length)
        console.error('Missing roles:', missingRoles);
    process.exit(1);
}
console.log('✅ Merged manifest passed SEO and role checks.');
