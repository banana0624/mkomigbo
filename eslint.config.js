// project-root/eslint.config.js
import { defineConfig } from 'eslint-define-config';

export default defineConfig({
  env: {
    browser: true,
    node: true,
    es2020: true
  },
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module'
  },
  plugins: ['react'],
  extends: ['eslint:recommended', 'plugin:react/recommended'],
  rules: {
    'no-unused-vars': 'warn',
    'react/react-in-jsx-scope': 'off'
  }
});
