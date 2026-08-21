# Project Description

This is the [Woodplane](https://Woodplane.com) Starter Theme. It has an object oriented PHP architecture and comes with a modern Vite-based build system.

# Getting started

It is distributed under the GNU General Public License v3.0. Or in short: **It's open source**. You are free to use this starter theme or only parts of it for personal and commercial use!

Just clone this repository and run a search for the following values and replace them with the values for your project.

```
Name:        Woodplane Theme
Key:         woodplane-theme
Namespace:   Woodplane
Prefix:      wdpln
```

## System requirements

### NodeJS

`node` and node package manager `npm` are required to run the build commands.

The project targets **Node 20 LTS** (see `.nvmrc`). If you use [nvm](https://github.com/nvm-sh/nvm), run `nvm use` to switch to the correct version.

Please visit [https://nodejs.org/en/download/](https://nodejs.org/en/download/) and download the latest LTS version of nodeJS.

# Build System

This theme uses [Vite](https://vitejs.dev/) as its build tool.

Install packages using `npm install`:

```
$ cd path/to/your/project/
$ npm install
```

## Available Commands

| Command | Description |
|---|---|
| `npm run build` | Production build — compiles SCSS, bundles JS, outputs to `build/` |
| `npm run dev` | Watch mode — rebuilds on file changes with live reload |
| `npm run svgo` | Optimize SVGs in `static/` |

## Feature overview

### Styles

This theme uses an [ITCSS architecture](https://www.creativebloq.com/web-design/manage-large-css-projects-itcss-101517528) together with the [BEM naming convention](http://getbem.com/). All `source/styles/*.scss` entry files are compiled to `build/styles/{name}.css` and `build/styles/{name}.min.css`.

The Package Class `Assets` enqueues them directly.

-   `admin-editor.min.css` is loaded in the backend. Generated from `source/styles/admin-editor.scss`.
-   `admin.min.css` is loaded in the backend. Generated from `source/styles/admin.scss`.
-   `ui.min.css` is loaded in the frontend. Generated from `source/styles/ui.scss`.

#### Gutenberg Blocks

Gutenberg blocks use JSX and require WordPress's dependency extraction, so they use a **separate build process** via [`@wordpress/scripts`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) (not Vite).

The block source is in `source/gutenberg/blocks.js`. Currently no custom blocks are active.

**When you need to add blocks**, install the WordPress build tooling:

```bash
npm install --save-dev @wordpress/scripts
```

Then add these scripts to `package.json`:

```json
"build:gutenberg": "wp-scripts build source/gutenberg/blocks.js --output-path=build/gutenberg",
"dev:gutenberg": "wp-scripts start source/gutenberg/blocks.js --output-path=build/gutenberg"
```

This will generate `build/gutenberg/blocks.js` and `build/gutenberg/blocks.asset.php`, which the `Gutenberg` PHP package enqueues automatically.

#### Gutenberg Editor Styles

The SCSS variable `$context` is defined in `admin-editor.scss` (value `edit`) and `ui.scss` (value `view`), so the mixins `context-view` and `context-edit` can generate CSS for the appropriate context:

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

### Scripts

This theme uses ES modules which are bundled using Vite (powered by Rollup). The Package Class `Assets` enqueues the resulting files. For example: all `source/scripts/ui/*.js` files will be bundled to `build/scripts/ui.js`. There is also a minified version `build/scripts/ui.min.js`.

### Fonts

There is a built in Font loading process using base64 encoded woff/woff2 fonts, which are stored inside the local storage of the browser. This avoids the FOUT problem.

Assuming that the fonts you're using are licensed for use in this way, convert the fonts to base64-encoded WOFF and WOFF2 CSS files using [Transfonter](https://transfonter.org/) and then add the code to the files in the `static/fonts` folder.

### Live Reload

In development mode (`npm run dev`), changes to PHP and Twig files will trigger a live page reload automatically via [vite-plugin-live-reload](https://github.com/nickmessing/vite-plugin-live-reload).

### SVG

SVG support and sanitization was formerly handled directly by the Theme. This feature was removed in 2020 in favour of https://wordpress.org/plugins/safe-svg/.

SVG optimization is handled by [SVGO](https://github.com/svg/svgo) via `npm run svgo`.

# Authors

-   [Kilian Sonnentrücker](https://github.com/kilianso)
-   [Nico Martin](https://github.com/nico-martin)
-   [Mark Howells-Mead](https://github.com/markhowellsmead/)
-   [Joel Stüdle](https://github.com/joel-st)
-   [Dimitri Suter](https://github.com/gnochi/)
