// project-root/utils/debounce.ts

export function debounce<T extends (...args: any[]) => void>(fn: T, delay = 300): T {
  let timer: NodeJS.Timeout;
  return function (...args: Parameters<T>) {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  } as T;
}
