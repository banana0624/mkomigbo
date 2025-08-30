// Scaffold: `project-root/src/context/AuditVizContext.tsx

import React, { createContext, useContext, useState } from 'react';

interface AuditVizContextType {
  dryRunOnly: boolean;
  setDryRunOnly: (value: boolean) => void;
  showDensity: boolean;
  setShowDensity: (value: boolean) => void;
}

const AuditVizContext = createContext<AuditVizContextType | undefined>(undefined);

export const AuditVizProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [dryRunOnly, setDryRunOnly] = useState(false);
  const [showDensity, setShowDensity] = useState(false);

  return (
    <AuditVizContext.Provider value={{ dryRunOnly, setDryRunOnly, showDensity, setShowDensity }}>
      {children}
    </AuditVizContext.Provider>
  );
};

export const useAuditViz = (): AuditVizContextType => {
  const context = useContext(AuditVizContext);
  if (!context) throw new Error('useAuditViz must be used within AuditVizProvider');
  return context;
};