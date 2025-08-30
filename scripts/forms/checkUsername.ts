// project-root/scripts/forms/checkUsername.ts

const takenUsernames = ['admin', 'root', 'test'];

export const checkUsernameAvailability = async (username: string): Promise<boolean> => {
  // Simulate async check
  await new Promise((res) => setTimeout(res, 300));
  return !takenUsernames.includes(username.toLowerCase());
};
