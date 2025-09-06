// project-root/domains/user/index.ts

export interface User {
  id: string;
  name: string;
  email: string;
}

export const currentUser: User = {
  id: 'u001',
  name: 'Theo',
  email: 'theo@example.com'
};
