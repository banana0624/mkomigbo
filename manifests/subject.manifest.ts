// project-root/manifests/subject.manifest.ts

export const routeManifest = {
  '/dashboard': {
    transition: { /* already defined */ },
    seo: {
      title: 'Dashboard | Mkomigbo',
      description: 'Overview of platform activity and insights.',
      keywords: ['dashboard', 'analytics', 'mkomigbo'],
    },
    access: {
      visitor: false,
      user: true,
      developer: true,
    },
  },
  '/about': {
    transition: { 
      /* ... */
     },
    seo: {
      title: 'About Us | Mkomigbo',
      description: 'Learn about our mission and team.',
      keywords: ['about', 'mission', 'team'],
    },
    access: {
      visitor: true,
      user: true,
      developer: true,
    },    
  },
}