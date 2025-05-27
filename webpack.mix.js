const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// PurgeCSS plugin
const PurgeCssPlugin = require('purgecss-webpack-plugin');
const glob = require('glob');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

mix.js('resources/js/app.js', 'public/js')
    .vue()
    .sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [
            require('autoprefixer'),
        ],
    })
    .version();

// Webpack config for code splitting and PurgeCSS
mix.webpackConfig({
    optimization: {
        splitChunks: {
            chunks: 'all',
        },
    },
    plugins: [
        new CleanWebpackPlugin(),
        ...(mix.inProduction() ? [
            new PurgeCssPlugin({
                paths: glob.sync(path.join(__dirname, 'resources/views/**/*.blade.php'), { nodir: true }),
                safelist: { standard: [/^bg-/, /^text-/, /^fa-/, /^btn-/, /^alert-/, /^modal/, /^show/, /^collapse/, /^fade/, /^carousel/, /^active/, /^in/, /^out/] },
            })
        ] : [])
    ]
});

// Disable notifications
mix.disableNotifications(); 