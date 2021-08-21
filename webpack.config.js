const path = require('path')
const webpack = require('webpack')
const cleanPlugin = require('clean-webpack-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const manifestPlugin = require('webpack-manifest-plugin')
const extractTextPlugin = require('extract-text-webpack-plugin')

const distPath = path.resolve(__dirname, './res/dist')
const publicPath = '../'

module.exports = {
    entry: {
        admin: ['./res/src/main.js', './res/src/assets/scss/Admin.scss'],
        frontend: ['babel-polyfill', './res/src/assets/js/Utils/closest.js', './res/src/assets/js/Frontend.js', './res/src/assets/scss/Base.scss'],
        popupBuilder: ['babel-polyfill', './res/src/assets/js/Utils/boxshadow.js', './res/src/assets/js/PopupBuilder.js'],
        pageBuilder: [ 'babel-polyfill' ],
        powerpack: './res/src/assets/js/Integrations/Powerpack.js',
        uabb: './res/src/assets/js/Integrations/UABB.js'
    },
    output: {
        path: distPath,
        publicPath: publicPath,
        filename: 'js/[name].[hash].js'
    },
    externals: {
        jquery: 'jQuery'
    },
    module: {
        rules: [
            {
                enforce: 'pre',
                test: /.vue$/,
                loader: 'eslint-loader',
                exclude: /node_modules/,
                options: {
                    fix: true
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    loaders: {
                        // Since sass-loader (weirdly) has SCSS as its default parse mode, we map
                        // the "scss" and "sass" values for the lang attribute to the right configs here.
                        // other preprocessors should work out of the box, no loader config like this nessessary.
                        'less': 'vue-style-loader!css-loader!less-loader',
                        'scss': 'vue-style-loader!css-loader!sass-loader',
                        'sass': 'vue-style-loader!css-loader!sass-loader?indentedSyntax'
                    }
                    // other vue-loader options go here
                }
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.scss$/,
                loader: extractTextPlugin.extract(['css-loader', 'sass-loader']),
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                loader: 'file-loader',
                options: {
                    name: '[name].[hash].[ext]'
                }
            }
        ]
    },
    plugins: [
        new manifestPlugin({
            filename: 'manifest.json'
        }),
        new cleanPlugin(distPath, {
            root: process.cwd(),
            verbose: false
        }),
        new extractTextPlugin({
            filename: 'css/[name].[hash].css'
        }),
        new CopyWebpackPlugin([
            {
                context: './res/src/assets/images',
                from: '**/*',
                to: 'images/'
            }
        ])
    ],
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            'vue-chayka-bootstrap': 'vue-chayka-bootstrap/dist/index.js'
        }
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true
    },
    performance: {
        hints: false
    },
    devtool: '#eval-source-map'
}

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = '#source-map'
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                warnings: false
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ])
}
