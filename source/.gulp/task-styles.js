import { src, dest } from 'gulp';

import cleanCSS from 'gulp-clean-css';
import filter from 'gulp-filter';

import dartSass from 'sass';
import gulpSass from 'gulp-sass';
const sass = gulpSass( dartSass );

import sassImportJson from 'gulp-sass-import-json';
import autoprefixer from 'gulp-autoprefixer';
import rename from 'gulp-rename';
// import livereload from 'gulp-livereload';
import browserSync from 'browser-sync';
import sourcemaps from 'gulp-sourcemaps';
import editorStyles from 'gulp-editor-styles';
import sassGlob from 'gulp-sass-glob';

export const task = config => {
    const blockFilter = filter(config.sourceDir + 'styles/admin-editor.css', { restore: true });

    return (
        src(config.sourceDir + 'styles/**/*.scss')
            .pipe(sassGlob())
            .pipe(sassImportJson({ cache: false, isScss: true }))
            .pipe(sourcemaps.init())
            .pipe(
                sass({
                    includePaths: ['./node_modules/'],
                }).on('error', sass.logError)
            )
            .pipe(sourcemaps.write({ includeContent: false }))
            .pipe(sourcemaps.init({ loadMaps: true }))
            .pipe(autoprefixer())
            .pipe(blockFilter) // filter stream so only admin-editor.css gets the editorStyles
            .pipe(editorStyles())
            .pipe(blockFilter.restore) // reset Filter
            .pipe(dest(config.buildDir + 'styles/'))
            .pipe(sourcemaps.write('.'))
            .on('error', config.errorLog)
            // minify
            .pipe(cleanCSS())
            .pipe(
                rename({
                    suffix: '.min',
                })
            )
            .on('error', config.errorLog)
            .pipe(dest(config.buildDir + 'styles/'))
            //reload
            .pipe(filter('**/*.css'))
            .pipe(browserSync.stream())
    );
};
