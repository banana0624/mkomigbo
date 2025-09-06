// project-root/vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@app': path.resolve(__dirname, 'app'),
      '@assets': path.resolve(__dirname, 'assets'),
      '@cli': path.resolve(__dirname, 'cli'),
      '@components': path.resolve(__dirname, 'components'),
      '@config': path.resolve(__dirname, 'config'),
      '@dashboard': path.resolve(__dirname, 'dashboard'),
      '@docs': path.resolve(__dirname, 'docs'),
      '@domains': path.resolve(__dirname, 'domains'),
      '@fixtures': path.resolve(__dirname, 'fixtures'),
      '@hooks': path.resolve(__dirname, 'hooks'),
      '@lib': path.resolve(__dirname, 'lib'),
      '@middleware': path.resolve(__dirname, 'middleware'),
      '@packages': path.resolve(__dirname, 'packages'),
      '@public': path.resolve(__dirname, 'public'),
      '@scripts': path.resolve(__dirname, 'scripts'),
      '@server': path.resolve(__dirname, 'server'),
      '@state': path.resolve(__dirname, 'state'),
      '@styles': path.resolve(__dirname, 'styles'),
      '@tests': path.resolve(__dirname, 'tests'),
      '@types': path.resolve(__dirname, 'types'),
      '@utils': path.resolve(__dirname, 'utils')
    }
  },
  server: {
    port: 3000,
    open: true
  }
});
