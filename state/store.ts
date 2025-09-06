// project-root/state/store.ts

import { create } from 'zustand';

interface AppState {
  user: string;
  setUser: (name: string) => void;
}

export const useAppState = create<AppState>((set) => ({
  user: '',
  setUser: (name) => set({ user: name })
}));
