// project-root/src/components/LayoutWrapper/LayoutWrapper.tsx

import React from 'react';
import theme from '../../styles/theme.module.css';

type LayoutWrapperProps = {
  children: React.ReactNode;
};

export const LayoutWrapper: React.FC<LayoutWrapperProps> = ({ children }) => {
  return (
    <div className={`${theme.bgLightContainer} ${theme.pMd} ${theme.mMd} ${theme.border}`}>
      {children}
    </div>
  );
};
