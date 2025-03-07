// Set up DOM environment
require('@testing-library/jest-dom');

// Mock jQuery
global.$ = global.jQuery = require('jquery');

// Mock WordPress globals
global.wp = {
    ajax: {
        post: jest.fn(),
        send: jest.fn()
    },
    i18n: {
        __: jest.fn(str => str),
        _x: jest.fn((str, context) => str),
        sprintf: jest.fn((str, ...args) => str)
    },
    hooks: {
        addFilter: jest.fn(),
        addAction: jest.fn(),
        doAction: jest.fn(),
        applyFilters: jest.fn(value => value)
    },
    editor: {
        initialize: jest.fn(),
        getContent: jest.fn()
    }
};

// Mock Fabric.js
global.fabric = {
    Canvas: jest.fn(() => ({
        add: jest.fn(),
        remove: jest.fn(),
        renderAll: jest.fn(),
        setWidth: jest.fn(),
        setHeight: jest.fn(),
        getObjects: jest.fn(() => []),
        on: jest.fn()
    })),
    Text: jest.fn(),
    Image: jest.fn(),
    Object: {
        prototype: {
            set: jest.fn(),
            setCoords: jest.fn()
        }
    }
};

// Mock WebFont
global.WebFont = {
    load: jest.fn()
};

// Mock plugin data
global.dallasDesignerData = {
    ajaxurl: '/wp-admin/admin-ajax.php',
    nonce: 'test-nonce',
    restUrl: '/wp-json/dallas-designer/v1',
    restNonce: 'test-rest-nonce',
    assetsUrl: '/wp-content/plugins/dallas-embroidery-designer/assets',
    debug: true
};

// Mock plugin translations
global.dallasDesignerTranslations = {
    general: {
        save: 'Save',
        cancel: 'Cancel',
        delete: 'Delete',
        loading: 'Loading...',
        error: 'Error',
        success: 'Success'
    },
    designer: {
        addText: 'Add Text',
        addImage: 'Add Image',
        addClipart: 'Add Clipart',
        removeBackground: 'Remove Background'
    }
};

// Mock canvas element
document.createElement('canvas').getContext = () => ({
    drawImage: jest.fn(),
    getImageData: jest.fn(() => ({
        data: new Uint8ClampedArray(100)
    })),
    putImageData: jest.fn()
});

// Mock file reader
global.FileReader = class {
    readAsDataURL() {
        setTimeout(() => {
            this.onload({ target: { result: 'data:image/png;base64,test' } });
        });
    }
};

// Mock fetch
global.fetch = jest.fn(() =>
    Promise.resolve({
        ok: true,
        json: () => Promise.resolve({}),
        blob: () => Promise.resolve(new Blob())
    })
);

// Mock local storage
const localStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
};
global.localStorage = localStorageMock;

// Mock session storage
const sessionStorageMock = {
    getItem: jest.fn(),
    setItem: jest.fn(),
    removeItem: jest.fn(),
    clear: jest.fn()
};
global.sessionStorage = sessionStorageMock;

// Mock console methods
global.console = {
    ...console,
    log: jest.fn(),
    error: jest.fn(),
    warn: jest.fn(),
    info: jest.fn()
};

// Mock timers
jest.useFakeTimers();

// Clean up after each test
afterEach(() => {
    // Clear all mocks
    jest.clearAllMocks();

    // Reset timers
    jest.clearAllTimers();

    // Clear storage
    localStorage.clear();
    sessionStorage.clear();

    // Clear document body
    document.body.innerHTML = '';

    // Reset AJAX handlers
    wp.ajax.post.mockReset();
    wp.ajax.send.mockReset();

    // Reset fetch mock
    fetch.mockClear();
});
