/*
 * Copyright (c) 2019.
 *
 * @author Syber
 */

const path = require('path');
const webpack = require('webpack');
const {VueLoaderPlugin} = require("vue-loader");
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: {
        'main': ['./main.js', './css/styles.scss'],
    },

    // Установка корневый путей для корректного поиска путей в импортах
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js'
        },
        modules: [
            path.resolve(__dirname, './src/'),
            path.resolve(__dirname, './node_modules/'),
        ],
    },

    context: path.resolve(__dirname, './src/'),

    output: {
        path: path.resolve(__dirname, '../public_html/dist'),
        publicPath: '/dist/',
        chunkFilename: '[name].[chunkhash:4].js',
        clean: true
    },

    devtool: 'source-map',

    performance: {
        hints: false
    },

    watchOptions: {
        ignored: /node_modules/
    },

    module: {
        rules: [
            // Загрузка файлов изображений, указанных в ссылках и импортах
            {
                test: /\.(png|jpe?g|gif|svg)$/i,
                type: 'asset',
                generator: {
                    filename: 'assets/images/[name].[contenthash:4][ext]'
                },
            },
            // Загрузка файлов шрифтов, указанных в ссылках и импортах
            {
                test: /\.(woff|woff2|ttf|eot)$/i,
                generator: {
                    filename: 'assets/fonts/[name].[contenthash:4][ext]'
                },
                type: 'asset'
            },
            {
                test: /\.(css)$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: 'css-loader',
                        options: {
                            url: {
                                filter: url => !url.startsWith('/img')
                            }
                        }
                    }
                ]
            },
            {
                test: /\.(scss)$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: 'css-loader',
                        options: {
                            url: {
                                filter: url => !url.startsWith('/img')
                            }
                        }
                    },
                    'sass-loader'
                ]
            },
            {
                test: /\.vue$/,
                use: "vue-loader"
            }
        ]

    },
    plugins: [
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            filename: 'css/[name].css'
        }),
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery'
        }),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: true,
            __VUE_PROD_DEVTOOLS__: false,
        }),
    ],
    optimization: {
        splitChunks: {
            filename: 'vendor/[name].js',
        }
    }
};
