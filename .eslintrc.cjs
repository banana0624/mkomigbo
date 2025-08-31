// project-root/.eslintrc.cjs

/* Enforces .js extensions in relative imports under node16/nodenext */
const path = require('path');

module.exports = {
  root: true,
  parser: '@typescript-eslint/parser',
  parserOptions: {
    project: [path.join(__dirname, 'tsconfig.json')],
    tsconfigRootDir: __dirname,
    sourceType: 'module',
    ecmaVersion: 'latest',
  },
  env: { es2022: true, node: true, browser: true },
  plugins: ['@typescript-eslint', 'import'],
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:import/recommended',
    'plugin:import/typescript',
  ],
  settings: {
    'import/resolver': {
      typescript: { alwaysTryTypes: true },
      node: { extensions: ['.js', '.jsx', '.ts', '.tsx', '.d.ts'] },
    },
  },
  rules: {
    // Require explicit extensions for relative imports (covers TS too)
    'import/extensions': ['error', 'always', {
      ignorePackages: true,
      js: 'always',
      mjs: 'always',
      ts: 'always',
      tsx: 'always',
    }],
    'import/no-unresolved': ['error', { commonjs: true, caseSensitive: true }],
  },
  overrides: [
    {
      files: ['*.ts', '*.tsx'],
      rules: {
        '@typescript-eslint/no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
      },
    },
  ],
};
