let mix                 = require( 'laravel-mix' );
let BrowserSyncPlugin   = require( 'browser-sync-webpack-plugin' );
const path              = require( "path" );
const ImageminPlugin    = require( 'imagemin-webpack-plugin' ).default;
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const imageminMozjpeg   = require( 'imagemin-mozjpeg' );
const DotenvPlugin      = require( 'webpack-dotenv-plugin' );
const jsonFile          = require( 'jsonfile' );
const _                 = require( 'lodash' );
const del               = require( 'del' );
const fs                = require( 'fs' );

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
const mixManifest = 'public/front/mix-manifest.json';

mix.webpackConfig( {
    plugins: [
        new DotenvPlugin( {
            sample          : './.env.example',
            path            : './.env',
            allowEmptyValues: true
        } ),
        new BrowserSyncPlugin( {
                proxy: process.env.APP_URL,
                files: [
                    'public/front/css/**/*.css',
                    'public/front/js/**/*.js',
                    '**/*.php',
                    '!resources/assets/**/*' ],
                open : false
            },
            {
                reload: false,
                notify: true
            }
        ),
        new CopyWebpackPlugin( [ {
            from : 'resources/assets/images',
            to   : 'images', // Laravel mix will place this in 'public/img',
            force: true
        } ] ),
        new CopyWebpackPlugin( [ {
            from: 'resources/assets/svg',
            to  : 'svg' // Laravel mix will place this in 'public/svg'
        } ] ),
        new ImageminPlugin( {
            test   : /\.(jpe?g|png|gif)$/i,
            plugins: [
                imageminMozjpeg( {
                    quality: 80
                } )
            ]
        } )
    ]
} );

mix.setResourceRoot( '/front/' )
    .setPublicPath( path.normalize( 'public/front' ) )
    .ts( 'resources/assets/js/app.ts', 'js' )
    .sass( 'resources/assets/sass/abovethefold.scss', 'css' )
    .sass( 'resources/assets/sass/style.scss', 'css' )
    .sass( 'resources/assets/sass/fonts.scss', 'css' )
    .options( {
        processCssUrls: false
    } );

if ( mix.inProduction() ) {
    mix.version()
        .then( versionAssets );
}
else {
    mix.sourceMaps();
}


function versionAssets() {
    jsonFile.readFile( mixManifest, ( err, obj ) => {
        const newJson = {};

        _.forIn( obj, ( value, key ) => {
            const newFilename = value.replace( /([^.]+)\.([^?]+)\?id=(.+)$/g, '$1.$3.$2' )
            const oldAsGlob   = value.replace( /([^.]+)\.([^?]+)\?id=(.+)$/g, '$1.*.$2' )
            del.sync( [ `public/front${oldAsGlob}` ] );
            fs.copyFile( `public/front${key}`, `public/front${newFilename}`, ( err ) => {
                if ( err ) {
                    console.error( err );
                }
            } );
            newJson[ key ] = newFilename;
        } );

        jsonFile.writeFile( mixManifest, newJson, { spaces: 4 }, ( err ) => {
            if ( err ) {
                console.error( err );
            }
        } )
    } )
}
