// project-root/scripts/utils/feedback.ts

export type FeedbackLevel = 'info' | 'success' | 'warning' | 'error';

export interface FeedbackMessage {
  id: string;
  text: string;
  level: FeedbackLevel;
  timestamp: number;
  context?: string;
}

/** Records feedback events for audit heatmaps and celebration triggers. */
export function postFeedback(message: string) {
  // TODO: wire to feedback bus
}

const feedbackBuffer: FeedbackMessage[] = [];

export const pushFeedback = (
  text: string,
  level: FeedbackLevel = 'info',
  context?: string
): FeedbackMessage => {
  const message: FeedbackMessage = {
    id: `msg-${Date.now()}-${Math.random().toString(36).slice(2)}`,
    text,
    level,
    timestamp: Date.now(),
    context,
  };
  feedbackBuffer.push(message);
  return message;
};

export const getFeedbackHistory = (): readonly FeedbackMessage[] => {
  return feedbackBuffer;
};

export const clearFeedback = (): void => {
  feedbackBuffer.length = 0;
};
