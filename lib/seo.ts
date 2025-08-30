// project-root/lib/seo.ts

export const injectSEO = (seo: {
  title: string
  description: string
  keywords: string[]
}) => {
  document.title = seo.title
  const metaDesc = document.querySelector('meta[name="description"]')
  const metaKeywords = document.querySelector('meta[name="keywords"]')

  if (metaDesc) metaDesc.setAttribute('content', seo.description)
  if (metaKeywords) metaKeywords.setAttribute('content', seo.keywords.join(', '))
}