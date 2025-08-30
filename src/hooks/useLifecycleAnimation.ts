// project-root/src/hooks/useLifecycleAnimation.ts

import { useEffect, useState } from 'react';

export function useLifecycleAnimation(state: string) {
  const [animatedState, setAnimatedState] = useState(state);

  useEffect(() => {
    setAnimatedState('');
    const timeout = setTimeout(() => setAnimatedState(state), 100);
    return () => clearTimeout(timeout);
  }, [state]);

  return animatedState;
}