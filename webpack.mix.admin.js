let mix = require('laravel-mix');
let path = require("path");

mix.webpackConfig({
    resolve: {
        modules: [
            path.resolve(__dirname, './node_modules'),
            path.resolve(__dirname, './vendor/arbory/arbory/resources/assets/js/')
        ]
    }
});

mix
    .sass('resources/assets/sass/admin.scss', 'public/arbory/css')
    .js('resources/assets/js/admin.js', 'public/js/');

require('./vendor/arbory/arbory/webpack.mix')(mix);
