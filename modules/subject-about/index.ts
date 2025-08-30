// project-root/modules/subject-about/index.ts

import AboutLayout from '../../layouts/aboutLayout'

export const SubjectAboutModule = {
  id: 'subject-about',
  title: 'About This Subject',
  layout: AboutLayout,
  lifecycle: {
    init: () => {
      console.log('SubjectAboutModule initialized')
    },
    destroy: () => {
      console.log('SubjectAboutModule destroyed')
    }
  }
}