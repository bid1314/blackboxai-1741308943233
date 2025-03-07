module.exports = {
    // Test environment setup
    testEnvironment: 'jsdom',
    setupFiles: ['<rootDir>/tests/js/setup.js'],
    
    // Test files pattern
    testMatch: [
        '<rootDir>/tests/js/**/*.test.js'
    ],
    
    // Module file extensions
    moduleFileExtensions: ['js', 'json'],
    
    // Module name mapper for CSS/SCSS imports
    moduleNameMapper: {
        '\\.(css|less|scss|sass)$': '<rootDir>/tests/js/__mocks__/styleMock.js',
        '\\.(jpg|jpeg|png|gif|eot|otf|webp|svg|ttf|woff|woff2|mp4|webm|wav|mp3|m4a|aac|oga)$': '<rootDir>/tests/js/__mocks__/fileMock.js'
    },
    
    // Coverage configuration
    collectCoverageFrom: [
        'public/js/**/*.js',
        'admin/js/**/*.js',
        '!**/node_modules/**',
        '!**/vendor/**',
        '!**/dist/**'
    ],
    coverageDirectory: 'coverage/js',
    coverageReporters: ['text', 'lcov'],
    
    // Transform configuration
    transform: {
        '^.+\\.js$': 'babel-jest'
    },
    
    // Global variables
    globals: {
        'jQuery': true,
        'wp': true,
        'fabric': true,
        'WebFont': true,
        'dallasDesignerData': true,
        'dallasDesignerTranslations': true
    },
    
    // Test timeout
    testTimeout: 10000,
    
    // Verbose output
    verbose: true,
    
    // Clear mocks between tests
    clearMocks: true,
    
    // Fail on console errors
    errorOnDeprecated: true,
    
    // Watch plugins
    watchPlugins: [
        'jest-watch-typeahead/filename',
        'jest-watch-typeahead/testname'
    ],
    
    // Reporter configuration
    reporters: [
        'default',
        ['jest-junit', {
            outputDirectory: 'reports',
            outputName: 'jest-junit.xml',
            ancestorSeparator: ' › ',
            usePathForSuiteName: true
        }]
    ]
};
