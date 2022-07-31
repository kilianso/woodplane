# Woodplane
A component based Wordpress starter theme based on Timber.

## Requirements
Node 
Composer 
A Local Wordpress Instance (since this is only the theme)

## Get started
1. Make sure you have [Node](https://nodejs.org/en/download/) installed.
2. If you are using [NVM (Node package manager)](https://github.com/nvm-sh/nvm), run `nvm use` to get and/or install the right Node Version for this project. The initial setup was done with Node `v14.17.1 (with npm 6.14.13)`
3. Then install the PHP dependencies using `composer install`.
3. Then install the JS dependencies and packages using `npm install`.
4. Then run `npm run dev` to start the dev server.
5. To run a build locally or on CI/CD run `npm run build`.

## Folder structure
- `base` – All the PHP classes, actions and hooks that are talking with Wordpress.
- `blocks` – PHP based Gutenberg Blocks that dont't make sense to migrate to React. (Dependency is the plugin [Genesis Blocks](https://www.studiopress.com/genesis-blocks/).)
- `languages` — i18n files for translations
- `source` — The source folder with all the shared and component based scripts, styles and views.
    - `gulp` – Everything related to the frontend buildtool.
    - `gutenberg` – React-based Gutenberg blocks
    - `scripts` – shared / general purpose scripts like helpers, utilities and so on.
    - `styles` – shared / general purpose scripts like helpers, utilities and so on.
    - `views` – Twig based components, partials and templates
- `static` – static assets like svgs, fonts and so on.
- `build` — The generated and minified output/assets after running through Gulp.
- `vendor` – [Composer](https://getcomposer.org/) based dependencies like [Timber](https://upstatement.com/timber/). Since this theme canno't work at all without Timber, it has been installed this way rather than relying on the plugin.

## Twig & Timber
Let's face it. Plain PHP sucks as a template language. So this theme uses [Twig](http://twig.sensiolabs.org/) as a template language thanks to the help of [Timber](https://upstatement.com/timber/). The basic concept is, that you still have your well known PHP files but when it comes to rendering Markup, you just pass the data to a Twig file, instead of doing that in PHP. To see examples, check some of the PHP files on root level of this theme and make sure you checkout the [Tibmer documentation.](https://timber.github.io/docs/)

### Twig Emmet support in VS Code
In order to use Emmet in your Twig files create a `.vscode` folder in the root directory of this theme. Then create a `settings.json` in it and put the following content in that file.

```json
{
    "files.associations": {
        "*.twig": "twig"
    },
    "emmet.includeLanguages": {
        "twig": "html"
    }
}
```

## Components & Partials
This setup let's you encapsulate Code into components. So Markup/Twig, Styles and Scripts live in one directory related to that component. As an example check `source/components/QuickNews`. There you have:

- QuickNews.twig
- QuickNews.scss
- QuickNews.js

⚠️ Beware that whenever you create a new component like this, the `scss` file will be automatically imported by Gulp into the entry file `styles/ui.scss`. However, if your component has a `js` file as well, you need to manually import it into the entry file in `scripts/index.js`.

If your component is getting complex, make sure you split things into subcomponents or, as named here, into partials. Thanks to Twig, you can include and pass data from and to partials.

## Dynamic imports
No one likes big bundle sizes. That's why this setup comes with dynamic imports thanks to Webpack. This will create JS chunks and load it on the fly if your component appears on a certain page. This should be the preferred approach if you have to load a big third party library like Mapbox etc. only on certain pages. Check `source/partials/CitySearch/Citysearch.js` to see an example.

## Styles
This theme uses [SASS/SCSS](https://sass-lang.com/) together with the [BEM naming convention](http://getbem.com/). All `source/assets/styles/*.scss` files will be converted to inside `assets/styles/{$name}.css` files.

The `base/Package` Class `Assets` enqueues them directly.

-   admin-editor.min.css is loaded in the backend. This file is generated from _source/styles/admin-editor.scss_.
-   admin.min.css is loaded in the backend. This file is generated from _source/styles/admin.scss_.
-   ui.min.css is loaded in the backend. This file is generated from _source/styles/ui.scss_.

## Gutenberg Blocks
Gutenberg blocks can be written in two ways. Either you do it "the React way" as shown in the examples in `/source/gutenberg/blocks/` or you do it "the PHP way" using a plugin like [Genesis Blocks](https://www.studiopress.com/genesis-blocks/). Whenever possible, i'd recommend to do it with React because of it's component based architecture and reactivity features. However, there are cases where the PHP way is just more straight forward and does the job.

The Theme is provided with built-in SCSS support for Gutenberg blocks. There is a specific `Gutenberg` Package (`base/Package/Gutenberg.php`) for some functionality.

The SCSS variable `$context` is defined in _admin-editor.scss_ (value `edit`) and _ui.scss_ (value `view`) appropriate to each context, so that the mixins `context-view` and `context-edit` can generate the CSS appropriately for the current context. For example:

```scss
.wp-block-image {
    vertical-align: middle;
}
@include context-view() {
    .wp-block-image {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
}
```

**Note** that the call to `@include context-view()` may not be defined _within_ the `.wp-block-image` definition, but must be included as a separate section. This is due to the CSS namespacing which occurs in the `gulp-editor-styles` process.

### gulp-editor-styles
Gulp uses the [gulp-editor-styles](https://www.npmjs.com/package/gulp-editor-styles) Node Module to automatically parse and wrap the CSS in an appropriate scope (`.editor-styles-wrapper`) for the Gutenberg editor.

## Scripts
This theme uses ES6 modules bundled or as dynamic imports using Webpack. The `base/Package` Class `Assets` enqueues the entry files. For example: all `source/scripts/ui/*.js` files will be bundled to `build/scripts/ui.js`. There will also be a minified version `build/scripts/ui.min.js`.

## Fonts
There is a built in Font loading process using base64 encoded woff/woff2 fonts, which are stored inside the local storage of the browser. This avoids the FOUT problem.

Assuming that the fonts you're using are licensed for use in this way, convert the fonts to base64-encoded WOFF and WOFF2 CSS files using [Transfonter](https://transfonter.org/) and then add the code to the files in the [assets/fonts](https://github.com/SayHelloGmbH/hello-roots/tree/master../../static/fonts) folder. Generate the WOFF and WOFF2 versions separately, as you'll need individual CSS files.

These files are then loaded [by JavaScript](https://github.com/SayHelloGmbH/hello-roots/blob/master/src/Package/Assets.php#L124) and stored in the browser's [LocalStorage](https://javascript.info/localstorage). The script checks the asset version number to respect new versions.

## Browsersync
As for now, this Theme uses [Browsersnc](http://browsersnc.io/) to refresh your browser on every change you make to `php` or `twig` files. For `js` and `scss` there is even some injection without full reloads.
