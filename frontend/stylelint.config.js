// Stylelint : CSS Modules en camelCase, variables du design system.
export default {
  extends: ['stylelint-config-standard'],
  ignoreFiles: ['**/node_modules/**', '**/build/**', '**/dist/**'],
  rules: {
    'selector-class-pattern': null,
    'custom-property-pattern': null,
    'keyframes-name-pattern': null,
    'selector-pseudo-class-no-unknown': [true, { ignorePseudoClasses: ['global'] }],
    'import-notation': 'string',
    // Safari exige encore la version préfixée de text-size-adjust.
    'property-no-vendor-prefix': [true, { ignoreProperties: ['text-size-adjust'] }],
    'declaration-empty-line-before': null,
    'no-descending-specificity': null,
  },
};
