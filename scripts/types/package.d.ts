// project-root/scripts/types/package.d.ts

declare module "*.json" {
  const value: {
    version: string;
  };
  export default value;
}