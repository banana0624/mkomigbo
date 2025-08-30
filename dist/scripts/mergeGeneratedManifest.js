// project-root/scripts/mergeGeneratedManifest.ts
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { routeManifest as canonicalManifest } from '../manifests/subject.manifest';
import generatedManifest from '../manifests/generated.manifest.json' assert { type: 'json' };
const args = process.argv.slice(2);
const forceRoutes = args.includes('--force')
    ? args.filter(arg => arg.startsWith('/'))
    : [];
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const outputPath = path.resolve(__dirname, '../manifests/merged.manifest.ts');
const merged = { ...canonicalManifest };
for (const [route, config] of Object.entries(generatedManifest)) {
    if (!merged[route]) {
        merged[route] = config;
        console.log(`🆕 Added new route: ${route}`);
    }
    else if (forceRoutes.includes(route)) {
        merged[route] = config;
        console.log(`⚡ Overwritten route: ${route}`);
    }
    else {
        console.warn(`⚠️ Skipped existing route: ${route}`);
        console.warn(`🔍 Diff:\n${JSON.stringify({ existing: canonicalManifest[route], incoming: config }, null, 2)}\n`);
    }
}
fs.writeFileSync(outputPath, `// Auto-generated merged manifest\n\nexport const routeManifest = ${JSON.stringify(merged, null, 2)};\n`);
console.log(`✅ Merged manifest written to ${outputPath}`);
