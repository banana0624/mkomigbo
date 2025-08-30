// project-root/src/types/vis-data.d.ts

declare module 'vis-data' {
  export class DataSet<T = any> {
    constructor(items?: T[]);
    add(item: T): void;
    get(id: string): T;
    // Add more methods as needed
  }
}