const { mix } = require( 'laravel-mix' );
const { env } = require( 'minimist' )( process.argv.slice( 2 ) );

if ( env && env.site ) {
    require( `${__dirname}/webpack.mix.${env.site}.js` );
}
