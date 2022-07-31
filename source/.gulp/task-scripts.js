import gulp from 'gulp';

import gulpWebpack from 'webpack-stream';
// import livereload from 'gulp-livereload';
import browserSync from 'browser-sync';

import rename from 'gulp-rename';
import uglify from 'gulp-uglify';
import fs from 'fs';

import babelloader from 'babel-loader';

function getDirectories(path) {
    return fs.readdirSync(path).filter(function (file) {
        return fs.statSync(path + '/' + file).isDirectory();
    });
}

export const task = config => {
    return new Promise(resolve => {
        const bundles = getDirectories(`${config.sourceDir}scripts/`);
        const entry = {};
        bundles.forEach(bundle => {
            const filePath = `${config.sourceDir}scripts/${bundle}/index.js`;
            if (fs.existsSync(filePath)) {
                entry[bundle] = './' + filePath;
            }
        });

        gulp.src([`${config.sourceDir}scripts/*`])
            // Webpack
            .pipe(
                gulpWebpack({
                    entry,
                    mode: 'production',
                    module: {
                        rules: [
                            {
                                test: /\.js$/,
                                exclude: /node_modules/,
                                loader: 'babel-loader',
                            },
                            {
                                test: /\.css$/i,
                                use: ['style-loader', 'css-loader'],
                            },
                        ],
                    },
                    output: {
                        filename: '[name].js',
                        publicPath: '/wp-content/themes/woodplane/build/scripts/'
                    }
                })
            )
            .on('error', config.errorLog)
            .pipe(gulp.dest(config.buildDir + 'scripts/'))

            // Minify
            .pipe(uglify())
            .pipe(
                rename({
                    suffix: '.min',
                })
            )
            .on('error', config.errorLog)
            .pipe(gulp.dest(config.buildDir + 'scripts/'))

            //reload
            .pipe(browserSync.stream());
        resolve();
    });
};
