import pluginVue from 'eslint-plugin-vue';
import tsParser from '@typescript-eslint/parser';
import { withVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import eslintConfigPrettier from 'eslint-config-prettier';

export default withVueTs(
    pluginVue.configs['flat/strongly-recommended'],
    vueTsConfigs.recommended,
    {
        files: ['**/*.js'],
        languageOptions: { parser: tsParser },
        rules: {
            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
        },
    },
    eslintConfigPrettier,
    { ignores: ['src/game/assets/tilemaps/tiledFiles/*.tsx'] },
    {
        rules: {
            'vue/max-attributes-per-line': ['error', { singleline: 3 }],
            'vue/require-default-prop': 'off',
            'no-trailing-spaces': 'warn',
            'no-var': 'error',
            'vue/no-deprecated-slot-attribute': 'off',
            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            '@typescript-eslint/ban-ts-comment': 'off',
            'vue/multi-word-component-names': 'off',
            'vue/no-reserved-component-names': 'off',
            'vue/block-lang': 'off',
            '@typescript-eslint/no-unused-expressions': ['error', { allowTernary: true }],
        },
    },
);
