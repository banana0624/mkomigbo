// project-root/modules/subject-history/index.ts

import AboutLayout from '../../layouts/aboutLayout'

export const SubjectHistoryModule = {
  id: 'subject-history',
  title: 'History of the Subject',
  layout: AboutLayout,
  lifecycle: {
    init: () => {
      console.log('SubjectHistoryModule initialized')
    },
    destroy: () => {
      console.log('SubjectHistoryModule destroyed')
    }
  }
}