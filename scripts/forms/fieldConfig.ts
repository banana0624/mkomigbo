// project-root/scripts/forms/fieldConfig.ts

export interface FieldConfig {
  name: string;
  label: string;
  type: 'text' | 'email' | 'password' | 'username';
  required?: boolean;
  minLength?: number;
  maxLength?: number;
  pattern?: RegExp;
  placeholder?: string;
  helpText?: string;
}

export const defaultFieldConfigs: FieldConfig[] = [
  {
    name: 'username',
    label: 'Username',
    type: 'username',
    required: true,
    minLength: 3,
    maxLength: 20,
    placeholder: 'Enter your username',
  },
  {
    name: 'email',
    label: 'Email',
    type: 'email',
    required: true,
    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    placeholder: 'you@example.com',
  },
  {
    name: 'password',
    label: 'Password',
    type: 'password',
    required: true,
    minLength: 8,
    helpText: 'Must include letters, numbers, and symbols',
  },
];
