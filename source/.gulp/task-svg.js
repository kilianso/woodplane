import gulp from 'gulp';

import rename from 'gulp-rename';
import svgmin from 'gulp-svgmin';
import livereload from 'gulp-livereload';

export const task = config => {
    return (
        gulp
            .src([config.staticDir + '**/*.svg', '!' + config.staticDir + '**/*.min.svg'])
            .pipe(
                svgmin({
                    plugins: [
                        {
                            removeViewBox: false,
                        },
                    ],
                })
            )
            .pipe(
                rename({
                    suffix: '.min',
                })
            )
            .on('error', config.errorLog)
            .pipe(gulp.dest(config.staticDir))
            //reload
            .pipe(livereload())
    );
};
