/**
 * Test helpers for JavaScript tests
 */

// Import testing utilities
import { fireEvent, waitFor } from '@testing-library/dom';

/**
 * Create a mock design object
 */
export const createMockDesign = (overrides = {}) => ({
    id: 'test-design-1',
    title: 'Test Design',
    design_data: {
        objects: [
            {
                type: 'text',
                text: 'Test Text',
                fontSize: 24,
                fontFamily: 'Arial',
                fill: '#000000',
                left: 100,
                top: 100
            }
        ],
        background: '#ffffff',
        width: 500,
        height: 500
    },
    mockup_url: 'http://example.com/mockup.png',
    product_id: 123,
    ...overrides
});

/**
 * Create a mock RFQ object
 */
export const createMockRFQ = (overrides = {}) => ({
    id: 'test-rfq-1',
    title: 'Test RFQ',
    design_data: createMockDesign().design_data,
    product_id: 123,
    quantity: 10,
    notes: 'Test notes',
    status: 'pending',
    ...overrides
});

/**
 * Create a mock product object
 */
export const createMockProduct = (overrides = {}) => ({
    id: 123,
    name: 'Test Product',
    price: '19.99',
    images: [
        {
            src: 'http://example.com/product.jpg'
        }
    ],
    attributes: {
        color: ['Red', 'Blue', 'Green'],
        size: ['S', 'M', 'L']
    },
    ...overrides
});

/**
 * Simulate canvas events
 */
export const simulateCanvasEvent = (canvas, eventType, options = {}) => {
    const event = new MouseEvent(eventType, {
        bubbles: true,
        cancelable: true,
        clientX: options.x || 0,
        clientY: options.y || 0,
        ...options
    });
    canvas.dispatchEvent(event);
};

/**
 * Wait for canvas to render
 */
export const waitForCanvas = async (canvas) => {
    await waitFor(() => {
        expect(canvas.getObjects().length).toBeGreaterThan(0);
    });
};

/**
 * Simulate file upload
 */
export const simulateFileUpload = async (input, file) => {
    Object.defineProperty(input, 'files', {
        value: [file]
    });
    fireEvent.change(input);
    await waitFor(() => {
        expect(input.files[0]).toBe(file);
    });
};

/**
 * Create mock API response
 */
export const createMockResponse = (data, status = 200) => ({
    ok: status >= 200 && status < 300,
    status,
    json: () => Promise.resolve(data)
});

/**
 * Mock REST API request
 */
export const mockRestRequest = (endpoint, response, method = 'GET') => {
    fetch.mockImplementationOnce((url, options = {}) => {
        if (url.includes(endpoint) && options.method === method) {
            return Promise.resolve(createMockResponse(response));
        }
        return Promise.reject(new Error('Not found'));
    });
};

/**
 * Mock AJAX request
 */
export const mockAjaxRequest = (action, response, success = true) => {
    wp.ajax.post.mockImplementationOnce((data) => {
        if (data.action === action) {
            return success
                ? Promise.resolve(response)
                : Promise.reject(new Error('AJAX error'));
        }
        return Promise.reject(new Error('Invalid action'));
    });
};

/**
 * Create mock event
 */
export const createMockEvent = (type = 'click', options = {}) => {
    const event = new Event(type, { bubbles: true, cancelable: true });
    Object.assign(event, options);
    return event;
};

/**
 * Wait for element to be removed
 */
export const waitForElementToBeRemoved = async (element) => {
    await waitFor(() => {
        expect(document.body.contains(element)).toBe(false);
    });
};

/**
 * Wait for notification
 */
export const waitForNotification = async (text) => {
    await waitFor(() => {
        const notification = document.querySelector('.dallas-designer-notification');
        expect(notification).toHaveTextContent(text);
    });
};

/**
 * Mock localStorage
 */
export const mockLocalStorage = () => {
    const store = {};
    return {
        getItem: (key) => store[key],
        setItem: (key, value) => { store[key] = value; },
        removeItem: (key) => { delete store[key]; },
        clear: () => { Object.keys(store).forEach(key => delete store[key]); }
    };
};

/**
 * Mock WebFont loader
 */
export const mockWebFontLoader = () => {
    WebFont.load = jest.fn((config) => {
        if (config.google && config.google.families) {
            setTimeout(() => config.active(), 100);
        }
    });
};

/**
 * Mock Fabric.js canvas
 */
export const mockFabricCanvas = () => {
    const objects = [];
    return {
        add: jest.fn((obj) => objects.push(obj)),
        remove: jest.fn((obj) => {
            const index = objects.indexOf(obj);
            if (index > -1) objects.splice(index, 1);
        }),
        getObjects: jest.fn(() => objects),
        renderAll: jest.fn(),
        setWidth: jest.fn(),
        setHeight: jest.fn(),
        on: jest.fn(),
        trigger: jest.fn()
    };
};

/**
 * Create test image
 */
export const createTestImage = (width = 100, height = 100) => {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    return canvas.toDataURL();
};

/**
 * Mock resize observer
 */
export const mockResizeObserver = () => {
    global.ResizeObserver = class ResizeObserver {
        observe() {}
        unobserve() {}
        disconnect() {}
    };
};
