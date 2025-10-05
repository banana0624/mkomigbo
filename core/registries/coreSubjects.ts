// project-root/core/registries/coreSubjects.ts

// Preloaded core subject manifests
import { registerSubject } from "./manifestRegistry.js";
import { SubjectManifest } from "./types.js";

const coreSubjects: Record<string, SubjectManifest> = {
  about: {
    id: "about",
    title: "About",
    description: "Learn more about our mission and team.",
    layout: "aboutLayout",
    meta: {
      pageTitle: "About Us",
      description: "Discover the story behind our platform.",
      keywords: ["about", "team", "mission"],
    },
  },
  contact: {
    id: "contact",
    title: "Contact",
    description: "Get in touch with us.",
    layout: "contactLayout",
    meta: {
      pageTitle: "Contact Us",
      description: "Reach out for support or inquiries.",
      keywords: ["contact", "support", "email"],
    },
  },
  // Add more core subjects here as needed
};

// Register all core subjects into the manifest registry
export function preloadCoreSubjects() {
  Object.entries(coreSubjects).forEach(([id, manifest]) => {
    registerSubject(id, manifest);
  });
}
