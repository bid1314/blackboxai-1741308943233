import { fireEvent, waitFor } from '@testing-library/dom';
import {
    createMockDesign,
    createMockProduct,
    mockFabricCanvas,
    mockWebFontLoader,
    simulateCanvasEvent,
    waitForCanvas
} from '../helpers';

// Mock the design tool module
jest.mock('../../../public/js/modules/design-tool', () => {
    return {
        __esModule: true,
        default: jest.fn().mockImplementation(() => ({
            initialize: jest.fn(),
            addText: jest.fn(),
            addImage: jest.fn(),
            addClipart: jest.fn(),
            removeBackground: jest.fn(),
            saveDesign: jest.fn(),
            loadDesign: jest.fn(),
            generateMockup: jest.fn(),
            destroy: jest.fn()
        }))
    };
});

describe('Design Tool', () => {
    let container;
    let canvas;
    let designTool;

    beforeEach(() => {
        // Set up DOM
        container = document.createElement('div');
        container.id = 'dallas-designer';
        document.body.appendChild(container);

        // Mock canvas
        canvas = mockFabricCanvas();
        fabric.Canvas.mockImplementation(() => canvas);

        // Mock WebFont loader
        mockWebFontLoader();

        // Import design tool
        designTool = require('../../../public/js/modules/design-tool').default;
    });

    afterEach(() => {
        document.body.removeChild(container);
        jest.clearAllMocks();
    });

    test('initializes correctly', () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        expect(instance.initialize).toHaveBeenCalled();
        expect(fabric.Canvas).toHaveBeenCalled();
    });

    test('adds text element', async () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const text = 'Test Text';
        instance.addText(text);

        expect(fabric.Text).toHaveBeenCalledWith(text, expect.any(Object));
        expect(canvas.add).toHaveBeenCalled();
        expect(canvas.renderAll).toHaveBeenCalled();
    });

    test('adds image element', async () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const imageUrl = 'test-image.jpg';
        instance.addImage(imageUrl);

        expect(fabric.Image.fromURL).toHaveBeenCalledWith(
            imageUrl,
            expect.any(Function),
            expect.any(Object)
        );
    });

    test('saves design', async () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const mockDesign = createMockDesign();
        instance.saveDesign();

        await waitFor(() => {
            expect(wp.ajax.post).toHaveBeenCalledWith({
                action: 'dallas_designer_save_design',
                design_data: expect.any(Object),
                product_id: expect.any(Number),
                nonce: expect.any(String)
            });
        });
    });

    test('loads design', async () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const mockDesign = createMockDesign();
        instance.loadDesign(mockDesign);

        await waitForCanvas(canvas);

        expect(canvas.clear).toHaveBeenCalled();
        expect(canvas.loadFromJSON).toHaveBeenCalledWith(
            mockDesign.design_data,
            expect.any(Function)
        );
    });

    test('generates mockup', async () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        instance.generateMockup();

        expect(canvas.toDataURL).toHaveBeenCalled();
    });

    test('handles canvas events', () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const canvasElement = document.querySelector('canvas');
        simulateCanvasEvent(canvasElement, 'mousedown', { x: 100, y: 100 });

        expect(canvas.trigger).toHaveBeenCalledWith('mouse:down', expect.any(Object));
    });

    test('updates object properties', () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const mockObject = {
            set: jest.fn(),
            setCoords: jest.fn()
        };

        canvas.getActiveObject.mockReturnValue(mockObject);

        instance.updateObjectProperties({
            fontSize: 24,
            fill: '#000000'
        });

        expect(mockObject.set).toHaveBeenCalledWith({
            fontSize: 24,
            fill: '#000000'
        });
        expect(canvas.renderAll).toHaveBeenCalled();
    });

    test('handles product color changes', () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const colorButton = document.createElement('button');
        colorButton.setAttribute('data-color', '#ff0000');
        container.appendChild(colorButton);

        fireEvent.click(colorButton);

        expect(instance.updateProductColor).toHaveBeenCalledWith('#ff0000');
    });

    test('handles view changes', () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        const viewButton = document.createElement('button');
        viewButton.setAttribute('data-view', 'back');
        container.appendChild(viewButton);

        fireEvent.click(viewButton);

        expect(instance.changeView).toHaveBeenCalledWith('back');
    });

    test('cleans up on destroy', () => {
        const instance = new designTool({
            container: '#dallas-designer',
            product: createMockProduct()
        });

        instance.destroy();

        expect(canvas.dispose).toHaveBeenCalled();
    });
});
