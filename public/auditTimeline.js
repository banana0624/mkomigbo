// project-root/public/auditTimeline.js

import { Timeline } from 'vis-timeline';
import { DataSet } from 'vis-data';

export async function renderAuditTimeline(container) {
  const res = await fetch('http://localhost:3001/audit');
  const auditData = await res.json();

  const items = new DataSet(
    auditData.map((entry, index) => ({
      id: index + 1,
      content: entry.mode === 'dry-run' ? '🧪 Dry Run' : '✅ Real',
      start: entry.timestamp,
      className: entry.mode === 'dry-run' ? 'dry-run' : 'real'
    }))
  );

  const options = {
    orientation: 'top',
    stack: false,
    margin: { item: 10, axis: 5 },
    editable: false
  };

  new Timeline(container, items, options);
}