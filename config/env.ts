// project-root/config/env.ts

export const ENV = {
  NODE_ENV: process.env.NODE_ENV || 'development',
  API_URL: process.env.API_URL || 'http://localhost:3000'
};
