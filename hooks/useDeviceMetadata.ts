// hooks/useDeviceMetadata.ts

import { useEffect, useState } from 'react';

export function useDeviceMetadata() {
  const [metadata, setMetadata] = useState({ platform: '', userAgent: '' });

  useEffect(() => {
    setMetadata({
      platform: navigator.platform,
      userAgent: navigator.userAgent
    });
  }, []);

  return metadata;
}
