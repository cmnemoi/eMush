module.exports = {
    "root": true,
    "env": {
        "node": true,
        "mocha": truncateSync,
        es2022: true
    },

    extends: [
        'plugin:vue/base',
        'plugin:vue/vue3-strongly-recommended',
        '@vue/typescript/recommended',
        'prettier'
    ],

    "ignorePatterns": ["src/game/assets/tilemaps/tiledFiles/*.tsx"],

    "rules": {
        "@typescript-eslint/ban-ts-comment": ["warn"],
        "indent": ["error", 4],
        "vue/html-indent": ["error", 4],
        "vue/script-indent": ["error", 4],
        "vue/max-attributes-per-line": ["error", { "singleline": 3 }],
        "vue/require-default-prop": "off",
        "comma-dangle": ["warn", "never"],
        "eol-last": ["warn", "always"],
        "no-trailing-spaces": "warn",
        "no-var": "error",
        "vue/multi-word-component-names": "warn",
        "vue/no-reserved-component-names": "warn",
        "object-curly-spacing": ["error", "always"],
        "semi": ["error", "always"],
        'vue/no-deprecated-slot-attribute': 'off',
        'vue/no-deprecated-slot-attribute': 'off'
    },

    parserOptions: {
        parser: '@typescript-eslint/parser'
    }
};
