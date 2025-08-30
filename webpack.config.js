// project-root/webpack.config.js

const path = require('path');

module.exports = {
  entry: './scripte/cli/flags/backup.ts',
  module: {
    rules: [{ test: /\.ts$/, use: 'ts-loader', exclude: /node_modules/ }]
  },
  resolve: {
    extensions: ['.ts', '.js'],
    alias: {
      '@utils': path.resolve(__dirname, 'scripte/cli/utils'),
      '@paths': path.resolve(__dirname, 'scripte/cli/paths')
    }
  },
  output: {
    filename: 'bundle.js',
    path: path.resolve(__dirname, 'dist')
  }
};