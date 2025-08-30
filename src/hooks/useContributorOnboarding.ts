// project-root/src/hooks/useContributorOnboarding.ts

import { useState, useEffect } from 'react';

interface OnboardingState {
  currentStep: number;
  completed: boolean;
  contributorId: string;
}

export const useContributorOnboarding = (contributorId: string) => {
  const [state, setState] = useState<OnboardingState>({
    currentStep: 0,
    completed: false,
    contributorId,
  });

  useEffect(() => {
    if (state.currentStep === 0) {
      console.log(`[onboarding] Started for ${contributorId}`);
    } else if (!state.completed) {
      console.log(`[onboarding] Step ${state.currentStep} completed`);
    }
  }, [state.currentStep, state.completed, contributorId]);

  const nextStep = () => {
    setState((prev) => {
      const next = prev.currentStep + 1;
      return {
        ...prev,
        currentStep: next,
        completed: next >= 4, // assuming 4 steps
      };
    });
  };

  const dismiss = () => {
    setState((prev) => ({ ...prev, completed: true }));
  };

  return {
    onboardingState: state,
    nextStep,
    dismiss,
  };
};
