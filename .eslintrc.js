// project-root/.eslintrc.js

module.exports = {
  rules: {
    'import/extensions': ['error', 'always', {
      js: 'never',
      ts: 'never'
    }],
    'import/no-unresolved': ['error', { ignore: ['\\.js$'] }]
  },
  overrides: [
    {
      files: ['*.ts', '*.tsx'],
      rules: {
        'import/extensions': ['error', 'always', { ts: 'always' }]
      }
    }
  ]
};
