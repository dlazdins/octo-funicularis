const
    gulp      = require( 'gulp' ),
    rename    = require( 'gulp-rename' ),
    cssfont64 = require( 'gulp-cssfont64' ),
    del       = require( 'del' )
;

const
    fontsSource     = 'resources/assets/fonts',
    fontDestination = 'resources/assets/sass/fonts'
;

gulp.task( "build", [ "fonts" ] );


/* FONTS */
gulp.task( 'fonts', function() {
    del( [
        fontDestination + '/**/*.*'
    ] );
    return gulp.src( fontsSource + '/*.ttf' )
        .pipe( cssfont64() )
        .pipe( rename( {
            prefix : '_',
            extname: '.scss'
        } ) )
        .pipe( gulp.dest( fontDestination ) );
} );
