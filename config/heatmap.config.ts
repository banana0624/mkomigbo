// project-root/config/heatmap.config.ts

export interface HeatmapTrace {
  id: string;
  timestamp: string;
  source: string;
  status: 'success' | 'failure' | 'pending';
  tags?: string[];
}

export const traceConfig: HeatmapTrace[] = [
  {
    id: 'init',
    timestamp: new Date().toISOString(),
    source: 'cli/init',
    status: 'success',
    tags: ['bootstrap', 'onboarding']
  },
  {
    id: 'trace',
    timestamp: new Date().toISOString(),
    source: 'cli/trace',
    status: 'success',
    tags: ['audit', 'overlay']
  }
];
