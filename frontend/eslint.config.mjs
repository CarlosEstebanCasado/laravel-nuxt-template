// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt({
  rules: {
    // Vue 3 supports multiple root nodes; Nuxt apps often legitimately use fragments.
    'vue/no-multiple-template-root': 'off',

    // Pragmatic for a starter template: allow `any` where iterating quickly.
    '@typescript-eslint/no-explicit-any': 'off',

    // Allow intentional unused values by prefixing with "_".
    '@typescript-eslint/no-unused-vars': ['error', {
      argsIgnorePattern: '^_',
      varsIgnorePattern: '^_',
      caughtErrorsIgnorePattern: '^_',
    }],
  },
})
