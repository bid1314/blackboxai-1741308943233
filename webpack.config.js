const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = (env, argv) => {
    const isDevelopment = argv.mode === 'development';

    return {
        entry: {
            'dallas-designer-public': './public/js/dallas-designer-public.js',
            'dallas-designer-admin': './admin/js/dallas-designer-admin.js',
            'dallas-designer-public-styles': './public/css/dallas-designer-public.css',
            'dallas-designer-admin-styles': './admin/css/dallas-designer-admin.css'
        },
        output: {
            filename: '[name].min.js',
            path: path.resolve(__dirname, 'dist'),
            clean: true
        },
        devtool: isDevelopment ? 'source-map' : false,
        module: {
            rules: [
                // JavaScript
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                },
                // CSS
                {
                    test: /\.css$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap: isDevelopment
                            }
                        }
                    ]
                }
            ]
        },
        optimization: {
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        format: {
                            comments: false
                        }
                    },
                    extractComments: false
                }),
                new CssMinimizerPlugin()
            ],
            splitChunks: {
                cacheGroups: {
                    commons: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all'
                    }
                }
            }
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: '[name].min.css'
            })
        ],
        resolve: {
            extensions: ['.js', '.css'],
            alias: {
                '@': path.resolve(__dirname),
                '@public': path.resolve(__dirname, 'public'),
                '@admin': path.resolve(__dirname, 'admin'),
                '@includes': path.resolve(__dirname, 'includes')
            }
        },
        performance: {
            hints: isDevelopment ? 'warning' : false,
            maxEntrypointSize: 512000,
            maxAssetSize: 512000
        },
        stats: {
            colors: true,
            modules: true,
            reasons: true,
            errorDetails: true
        },
        watchOptions: {
            ignored: /node_modules/
        }
    };
};
