// project-root/src/components/OnboardingOverlay/OnboardingOverlay.tsx

import React from 'react';
import theme from '../../styles/theme.module.css';

type Props = {
  message?: string;
  visible: boolean;
};

export const OnboardingOverlay: React.FC<Props> = ({ message = 'Welcome aboard!', visible }) => {
  if (!visible) return null;

  return (
    <div className={`${theme.bgPrimary} ${theme.pMd} ${theme.transition} ${theme.fadeIn}`}>
      <h2 className={theme.textInfo}>{message}</h2>
    </div>
  );
};
