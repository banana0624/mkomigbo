// project-root/core/registries/types.ts

// types.ts or wherever SubjectManifest is defined
export interface SubjectManifest {
  id: string;
  title: string;
  description?: string;
  layout: string;
  hooks?: string[];
  overlays?: string[];
  roles?: string[];
  meta?: {
    pageTitle?: string;
    description?: string;
    keywords?: string[];
    subject?: string;
  };
}

export interface StyleManifest {
  id: string;
  name: string;
  cssPath: string;
  appliesTo: string[]; // subject IDs
}

export interface MediaManifest {
  id: string;
  type: "image" | "video" | "audio";
  sourcePath: string;
  altText?: string;
  linkedSubjects?: string[];
}

// Lifecycle hook type
export type LifecycleHook = () => void;
