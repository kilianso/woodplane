import gulp from 'gulp';
// import livereload from 'gulp-livereload';
import browserSync from 'browser-sync';
browserSync.create();

const config = {
    name: 'Woodplane Theme',
    key: 'wdpln',
    sourceDir: 'source/',
    staticDir: 'static/',
    buildDir: 'build/',
    proxy: 'woodplane.local', // to use it with Flywheel Local URLs
    errorLog: function (error) {
        console.log('\x1b[31m%s\x1b[0m', error);
        if(this.emit) {
            this.emit('end');
        }
    },
    reload: ['*.php', '{source,base}/**/*.{php,html,twig}'],
};

import { task as taskStyles } from './source/.gulp/task-styles.js';
import { task as taskScripts } from './source/.gulp/task-scripts';
import { task as taskSvg } from './source/.gulp/task-svg';
import { task as taskPot } from './source/.gulp/task-pot';
import { task as taskServe } from './source/.gulp/task-serve';
import { task as taskGutenberg } from './source/.gulp/task-gutenberg';

export const styles = () => taskStyles(config);
export const scripts = () => taskScripts(config);
export const svg = () => taskSvg(config);
export const pot = () => taskPot(config);
export const gutenberg = () => taskGutenberg(config);
export const serve = () => taskServe(config);
export const watch = () => {
    // browsersync
    gulp.watch(config.sourceDir + '**/**/*.scss', gulp.series(styles));
    gulp.watch(config.sourceDir + '**/**/*.{scss,css,js}', gulp.series(scripts));
    gulp.watch(config.sourceDir + 'gutenberg/**/*.{scss,css,js,jsx}', gulp.series(gutenberg));

    // full reload
    gulp.watch([config.staticDir + '**/*.svg', '!' + config.staticDir + '**/*.min.svg'], gulp.series(svg)).on('change', browserSync.reload);
    gulp.watch(config.reload).on('change', browserSync.reload);
};

export const dev = gulp.series(gulp.parallel(serve, styles, scripts, svg), watch);
export const build = gulp.series(gulp.parallel(gutenberg, styles, scripts, svg));
export default dev;
