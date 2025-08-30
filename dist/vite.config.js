// project-root/vite.config.ts
import { defineConfig } from 'vite';
import path from 'path';
export default defineConfig({
    resolve: {
        alias: {
            '@utils': path.resolve(__dirname, 'scripte/cli/utils'),
            '@paths': path.resolve(__dirname, 'scripte/cli/paths')
        }
    }
});
