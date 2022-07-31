import browserSync from 'browser-sync';

export const task = config => {
    return new Promise(resolve => {
        browserSync.init({
            proxy: config.proxy,
            notify: false
        });

        resolve();
    });
};
