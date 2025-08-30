// project-root/subjects/history/formEnhancers.ts

import { FieldConfig } from '../../scripts/forms/fieldConfig';

export const enhanceHistoryForm = (fields: FieldConfig[]): FieldConfig[] => {
  return fields.map((field) => {
    if (field.name === 'username') {
      return {
        ...field,
        helpText: 'Choose a username that reflects your historical interests',
        placeholder: 'e.g. historyBuff42',
      };
    }
    return field;
  });
};
