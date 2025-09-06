// project-root/tests/integration/api.test.ts

import { describe, it, expect } from 'vitest';
import { getStatus } from '@server/api';

describe('API Integration', () => {
  it('should return status ok', () => {
    const result = getStatus();
    expect(result.status).toBe('ok');
  });
});
