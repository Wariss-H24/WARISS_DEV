// ESLint du monorepo : TypeScript, règles des hooks React, accessibilité (jsx-a11y).
import js from '@eslint/js';
import jsxA11y from 'eslint-plugin-jsx-a11y';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
  { ignores: ['**/node_modules/**', '**/build/**', '**/dist/**', '**/.react-router/**', '**/schema.d.ts'] },
  js.configs.recommended,
  ...tseslint.configs.recommended,
  {
    // Scripts et fichiers de configuration exécutés par Node
    files: ['**/*.{js,mjs,cjs}'],
    languageOptions: { globals: globals.node },
  },
  {
    files: ['**/*.{ts,tsx}'],
    languageOptions: { globals: { ...globals.browser, ...globals.node } },
    plugins: { 'react-hooks': reactHooks, 'jsx-a11y': jsxA11y },
    rules: {
      ...reactHooks.configs.recommended.rules,
      ...jsxA11y.flatConfigs.recommended.rules,
      '@typescript-eslint/consistent-type-imports': ['error', { fixStyle: 'inline-type-imports' }],
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      // Le terminal et la palette donnent le focus volontairement à leur champ à l'ouverture.
      'jsx-a11y/no-autofocus': 'off',
    },
  },
);
