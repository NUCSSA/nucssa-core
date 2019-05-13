module.exports = {
    'env': {
        'browser': true,
        'commonjs': true,
        'es6': true,
        'node': true
    },
    'extends': [
        'eslint:recommended',
        'plugin:react/recommended'
    ],
    'parser': 'babel-eslint',
    'parserOptions': {
        'ecmaVersion': 6,
        'ecmaFeatures': {
            'experimentalObjectRestSpread': true,
            'jsx': true
        },
        'sourceType': 'module'
    },
    'plugins': [
        'react'
    ],
    'rules': {
        'indent': [
            'error',
            2,
            {
                'SwitchCase': 1
            }
        ],
        'quotes': [
            'error',
            'single'
        ],
        'linebreak-style': [
            'error',
            'unix'
        ],
        'semi': [
            'error',
            'always',
            { "omitLastInOneLineBlock": true }
        ],
        'func-style': [
            'error',
            'expression'
        ],
        'comma-dangle': [
            'error',
            'never'
        ],
    }
};
