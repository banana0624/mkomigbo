// hooks/useWelcomeOverlay.ts

import { useEffect } from 'react';

export function useWelcomeOverlay() {
  useEffect(() => {
    console.log('🎉 Welcome overlay activated');
  }, []);
}
