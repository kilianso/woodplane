import gulp from 'gulp';

import modernizr from 'gulp-modernizr';
import fs from 'fs';
import rename from 'gulp-rename';
import uglify from 'gulp-uglify';

function getDirectories(path) {
    return fs.readdirSync(path).filter(function (file) {
        return fs.statSync(path + '/' + file).isDirectory();
    });
}

export const task = config => {
    return new Promise(resolve => {
        const bundles = getDirectories(`${config.sourceDir}scripts/`);
        let loaded = 0;
        bundles.forEach(bundle => {
            gulp.src([`${config.buildDir}scripts/${bundle}.js`])
                .pipe(modernizr(`${bundle}-modernizr.js`))

                // Minify
                .pipe(uglify())
                .pipe(
                    rename({
                        suffix: '.min',
                    })
                )
                .on('error', config.errorLog)
                .pipe(gulp.dest(config.buildDir + 'scripts/modernizr/'));
            loaded++;
            if (loaded === bundles.length) {
                resolve();
            }
        });
    });
};
