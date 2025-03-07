module.exports = {
    extends: ['@commitlint/config-conventional'],
    rules: {
        'type-enum': [
            2,
            'always',
            [
                'feat',     // New feature
                'fix',      // Bug fix
                'docs',     // Documentation only changes
                'style',    // Changes that do not affect the meaning of the code
                'refactor', // Code change that neither fixes a bug nor adds a feature
                'perf',     // Code change that improves performance
                'test',     // Adding missing tests or correcting existing tests
                'chore',    // Changes to the build process or auxiliary tools
                'revert',   // Reverts a previous commit
                'wip'       // Work in progress
            ]
        ],
        'type-case': [2, 'always', 'lower'],
        'type-empty': [2, 'never'],
        'scope-case': [2, 'always', 'lower'],
        'subject-case': [2, 'always', 'sentence-case'],
        'subject-empty': [2, 'never'],
        'subject-full-stop': [2, 'never', '.'],
        'header-max-length': [2, 'always', 72],
        'body-leading-blank': [2, 'always'],
        'footer-leading-blank': [2, 'always']
    }
};
