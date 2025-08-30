// project-root/src/hooks/useBadgePulse.ts

import { useEffect, useState } from 'react';

export function useBadgePulse(trigger: boolean) {
  const [active, setActive] = useState(false);

  useEffect(() => {
    if (trigger) {
      setActive(true);
      const timeout = setTimeout(() => setActive(false), 800);
      return () => clearTimeout(timeout);
    }
  }, [trigger]);

  return active ? 'badgePulse' : '';
}
