// project-root/public/lifecycleTimeline.js

import { Timeline } from 'vis-timeline';
import { DataSet } from 'vis-data';

export async function renderLifecycleTimeline(container, dryRun = false) {
  const res = await fetch(`http://localhost:3001/summary?dryRun=${dryRun}`);
  const summary = await res.json();

  const items = new DataSet(
    summary.entries.map((entry, index) => ({
      id: index + 1,
      content: `${entry.id} (${entry.stage})`,
      start: entry.timestamp,
      className: entry.stage
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

import { DataSet, Timeline } from 'vis-timeline/standalone';

export function renderLifecycleTimeline(container, lifecycleEvents) {
  const items = new DataSet(
    lifecycleEvents.map((event, index) => ({
      id: index,
      content: `${event.stage.toUpperCase()} (${event.id})`,
      start: event.timestamp,
      className: event.stage,
    }))
  );

  const options = {
    stack: false,
    orientation: 'top',
    selectable: false,
  };

  new Timeline(container, items, options);
}