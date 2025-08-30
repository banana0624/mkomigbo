/* project-root/public/script.js */

import { DataSet, Timeline } from 'vis-timeline/standalone';
import { renderAuditTimeline } from './auditTimeline.js';
import { getAuditEvents } from './dataLoader.js'; // hypothetical

import { renderLifecycleTimeline } from './lifecycleTimeline.js';
import { getLifecycleEvents } from './dataLoader.js'; // hypothetical

const lifecycleEvents = getLifecycleEvents(); // Load from manifest or mock
renderLifecycleTimeline(document.getElementById('lifecycleTimeline'), lifecycleEvents);

// Render audit mode switches timeline
const auditEvents = getAuditEvents(); // Load from manifest or mock
renderAuditTimeline(document.getElementById('auditTimeline'), auditEvents);

// Render audit table
function renderAuditTable(data) {
  const container = document.getElementById('auditTable');
  container.innerHTML = `
    <table border="1" cellpadding="5">
      <tr><th>Timestamp</th><th>Mode</th></tr>
      ${data.map(entry => `
        <tr>
          <td>${entry.timestamp}</td>
          <td>${entry.mode}</td>
        </tr>
      `).join('')}
    </table>
  `;
}

fetch('http://localhost:3001/audit')
  .then(res => res.json())
  .then(renderAuditTable);

// Render backup timeline
function renderBackupTimeline(container, backups) {
  const items = new DataSet(
    backups.map((entry, index) => ({
      id: index,
      content: entry.label,
      start: entry.timestamp,
      className: entry.stage || 'created',
    }))
  );

  const options = {
    stack: false,
    orientation: 'top',
    selectable: false,
  };

  new Timeline(container, items, options);
}

// Update timeline based on dry-run toggle
async function updateTimeline(dryRun) {
  const container = document.getElementById('timeline');
  container.classList.add('fade-out');

  setTimeout(async () => {
    const summary = await fetchSummary(dryRun);
    container.innerHTML = '';
    const backups = summary.entries.map(entry => ({
      id: entry.id,
      timestamp: entry.timestamp,
      label: `${entry.id} (${entry.size}) — ${entry.stage}`,
      stage: entry.stage,
    }));
    renderBackupTimeline(container, backups);
    container.classList.remove('fade-out');
  }, 500);
}
