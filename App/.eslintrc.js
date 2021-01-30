module.exports = {
    "root": true,
    "env": {
        "node": true,
        "mocha": true
    },
    "extends": [
        "plugin:vue/vue3-recommended",
        "eslint:recommended"
    ],
    "parserOptions": {
        "parser": "babel-eslint"
    },
    "rules": {
        "indent": ["warn", 4],
        "vue/html-indent": ["warn", 4],
        "vue/script-indent": ["warn", 4],
        "vue/max-attributes-per-line": ["warn", { "singleline": 3 }],
        "vue/require-default-prop": "off",
        "comma-dangle": ["warn", "never"],
        "eol-last": ["warn", "always"],
        "no-trailing-spaces": "warn",
        "no-var": "warn",
        "object-curly-spacing": ["warn", "always"],
        "semi": ["warn", "always"]
    }
}
