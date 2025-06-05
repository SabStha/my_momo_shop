const mix = require('laravel-mix');
const path = require('path');
const glob = require('glob');
const webpack = require('webpack');

// Plugins
const PurgeCSSPlugin = require('purgecss-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

mix.js('resources/js/app.js', 'public/js')
   .vue()
   .sass('resources/sass/app.scss', 'public/css')
   .options({
       processCssUrls: false,
       postCss: [
           require('tailwindcss'),
           require('autoprefixer'),
       ],
   })
   .version()
   .disableNotifications();

// Custom Webpack config
mix.webpackConfig({
    optimization: {
        splitChunks: {
            chunks: 'all',
        },
    },
    plugins: [
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: [
                'public/js/*',
                'public/css/*'
            ],
            verbose: true
        }),
        new webpack.DefinePlugin({
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
            __VUE_OPTIONS_API__: 'true',
            __VUE_PROD_DEVTOOLS__: 'false'
        }),
        ...(mix.inProduction() ? [
            new PurgeCSSPlugin({
                paths: glob.sync(path.join(__dirname, 'resources/views/**/*.blade.php'), { nodir: true }),
                safelist: {
                    standard: [
                        /^bg-/, /^text-/, /^fa-/, /^btn-/, /^alert-/,
                        /^modal/, /^show/, /^collapse/, /^fade/,
                        /^carousel/, /^active/, /^in/, /^out/
                    ]
                },
            })
        ] : [])
    ]
});
