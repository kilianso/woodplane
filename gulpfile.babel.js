import gulp from 'gulp';
import livereload from 'gulp-livereload';

const config = {
    name: 'Woodplane Theme',
    key: 'wdpln',
    sourceDir: 'source/',
    staticDir: 'static/',
    buildDir: 'build/',
    errorLog: function (error) {
        console.log('\x1b[31m%s\x1b[0m', error);
        if(this.emit) {
            this.emit('end');
        }
    },
    reload: ['*.php', '{source,base}/**/*.{php,html,twig}'],
};

import { task as taskStyles } from './source/.gulp/task-styles';
import { task as taskScripts } from './source/.gulp/task-scripts';
import { task as taskReload } from './source/.gulp/task-reload';
import { task as taskSvg } from './source/.gulp/task-svg';
import { task as taskModernizr } from './source/.gulp/task-modernizr';
import { task as taskPot } from './source/.gulp/task-pot';
import { task as taskServe } from './source/.gulp/task-serve';
import { task as taskGutenberg } from './source/.gulp/task-gutenberg';

export const styles = () => taskStyles(config);
export const scripts = () => taskScripts(config);
export const reload = () => taskReload(config);
export const svg = () => taskSvg(config);
export const modernizr = () => taskModernizr(config);
export const pot = () => taskPot(config);
export const gutenberg = () => taskGutenberg(config);
export const serve = () => taskServe(config);
export const watch = () => {
    livereload.listen();
    gulp.watch(config.sourceDir + '**/**/*.scss', gulp.series(styles));
    gulp.watch(config.sourceDir + '**/**/*.{scss,css,js}', gulp.series(scripts));
    gulp.watch(config.sourceDir + 'gutenberg/**/*.{scss,css,js,jsx}', gulp.series(gutenberg));
    gulp.watch([config.staticDir + '**/*.svg', '!' + config.staticDir + '**/*.min.svg'], gulp.series(svg));
    gulp.watch(config.reload).on('change', livereload.changed);
};

export const taskDefault = gulp.series(gulp.parallel(styles, scripts, reload, svg), watch);
export default taskDefault;