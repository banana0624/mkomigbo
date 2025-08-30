// project-root/src/browser/components/BackupTimeline.ts

import { Timeline } from 'vis-timeline';
import { DataSet } from 'vis-data'; // ✅ Correct source for DataSet
import 'vis-timeline/styles/vis-timeline-graph2d.min.css';

export function renderBackupTimeline(
  container: HTMLElement,
  backups: Array<{ id: string; timestamp: string; label: string }>
) {
  const items = new DataSet(
    backups.map((backup) => ({
      id: backup.id,
      content: backup.label,
      start: backup.timestamp,
    }))
  );

  const options = {
    stack: false,
    showMajorLabels: true,
    orientation: 'top',
    editable: false,
    margin: { item: 10, axis: 5 },
  };

  new Timeline(container, items, options);
}
