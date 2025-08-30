// project-root/scripts/utils/feedbackRenderer.ts

import type { FeedbackMessage, FeedbackLevel } from './feedback';
import { getFeedbackHistory, pushFeedback, clearFeedback } from './feedback';

const TOAST_CONTAINER_ID = 'feedback-toasts';

const hasDOM = () => typeof document !== 'undefined';

const ensureContainer = (id: string): HTMLElement | null => {
  if (!hasDOM()) return null;
  let el = document.getElementById(id) as HTMLElement | null;
  if (!el) {
    el = document.createElement('div');
    el.id = id;
    document.body.appendChild(el);
  }
  return el;
};

/** Renders onboarding overlays and celebration badges from feedback signals. */
export function renderFeedbackOverlay() {
  // TODO: implement mount/unmount overlay
}

export const mountToastContainer = (): void => {
  const el = ensureContainer(TOAST_CONTAINER_ID);
  if (el) el.classList.add('toast-container');
};

export const renderToast = (msg: FeedbackMessage): void => {
  const container = ensureContainer(TOAST_CONTAINER_ID);
  if (!container || !hasDOM()) return;

  const div = document.createElement('div');
  div.className = `toast toast-${msg.level}`;
  div.setAttribute('data-id', msg.id);
  div.textContent = msg.text;

  container.appendChild(div);
  setTimeout(() => {
    div.classList.add('toast-exit');
    setTimeout(() => div.remove(), 300);
  }, 2500);
};

export const replayFeedback = (): void => {
  getFeedbackHistory().forEach(renderToast);
};

export const resetFeedbackUI = (): void => {
  clearFeedback();
  if (!hasDOM()) return;
  const container = document.getElementById(TOAST_CONTAINER_ID);
  if (container) container.innerHTML = '';
};
