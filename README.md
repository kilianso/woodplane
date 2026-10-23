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

The project targets **Node 24 LTS** (see `.nvmrc`). If you use [nvm](https://github.com/nvm-sh/nvm), run `nvm use` to switch to the correct version.

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
| `npm run dev` | Watch mode — rebuilds on file changes (reload the browser manually) |
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

This theme uses ES modules which are bundled using Vite (powered by Rolldown). The Package Class `Assets` enqueues the resulting files as `<script type="module">`. Dynamic imports become chunks in `build/scripts/`, loaded only when needed. For example: all `source/scripts/ui/*.js` files will be bundled to `build/scripts/ui.js`. There is also a minified version `build/scripts/ui.min.js`.

### Plugins

The theme must keep working when a plugin is missing or deactivated. Guard plugin functions called from Twig, otherwise they are a fatal error without the plugin:

```twig
{% if function('function_exists', 'plugin_function') %}
    {{ function('plugin_function') }}
{% endif %}
```

Use `class_exists` for classes (also for `'Class::method'` calls). In PHP, load plugin-specific packages only when the plugin is active, as `Theme.php` does for WooCommerce and Jetpack.

### Fonts

Fonts are self-hosted (no requests to Google). `source/styles/base/_fontimport.scss` declares them with `font-display: swap`, so text shows in the fallback font until the webfont is loaded; Vite copies the woff2 files to `build/`. Preload fonts used on every page with `<link rel="preload" as="font" type="font/woff2" crossorigin>` in the HTML head. For Google Fonts, use the matching [Fontsource](https://fontsource.org/) npm package (e.g. `@import '@fontsource/playfair-display/500';`) instead of `fonts.googleapis.com`.

### SVG

SVG support and sanitization was formerly handled directly by the Theme. This feature was removed in 2020 in favour of https://wordpress.org/plugins/safe-svg/.

SVG optimization is handled by [SVGO](https://github.com/svg/svgo) via `npm run svgo`.

## Deployment

[Build and deploy theme](.github/workflows/deploy.yml) builds the theme on GitHub and uploads it to the server on every push to `main`, or manually from the **Actions** tab. `build/` and `vendor/` are not in git; the workflow creates them with `npm run build` and `composer install --no-dev`.

Until deployment is set up, nothing is uploaded: the run stays green, the deploy job shows as skipped, and the run summary lists what's missing. Only an invalid `DEPLOY_METHOD` value fails the run.

### Set it up

1. **Choose the method.** In the `env` block at the top of the workflow, set `DEPLOY_METHOD` to `ssh` (rsync over SSH: needs shell access and rsync on the server, transfers only changed files) or `sftp` (works with SFTP-only accounts).
2. **Set the target** in the same block: `DEPLOY_HOST`, `DEPLOY_PORT`, `DEPLOY_USER` and `DEPLOY_PATH` (the theme directory on the server, absolute or relative to the login directory). Set `SSH_OPTIONS` only if the server needs it, e.g. `-o HostKeyAlgorithms=+ssh-rsa`. These values are not secret.
3. **Add the credential on GitHub** under **Settings → Secrets and variables → Actions → New repository secret**, either:
    - `DEPLOY_PASSWORD`, or
    - `DEPLOY_SSH_KEY`: the private key, including the `-----BEGIN`/`END` lines, plus `DEPLOY_SSH_KEY_PASSPHRASE` if the key has one. Use a dedicated deploy key, with its public key authorized on the server.
4. **Pin the server's host key**, so the workflow only connects to the real server: run `ssh-keyscan -p <port> <host> | grep -v '^#' > .github/known_hosts`, check that `ssh-keygen -lf .github/known_hosts` shows the fingerprints your hosting provider publishes, and commit the file.
5. **Deploy** by pushing to `main` or with **Run workflow** in the **Actions** tab, and check the log of the first run.

To deploy from another branch, replace `main` under `on.push.branches` and in the `check` job's `if`.

### How it uploads

The upload adds or replaces files. `build/` and `vendor/` are mirrored exactly, so files the build no longer produces (e.g. old hashed chunks) are deleted on the server; nothing else is deleted. The theme is updated in place, not switched atomically.

### Status

Woodplane itself isn't deployed: `DEPLOY_METHOD` is still a placeholder, so every run skips the upload. Themes based on Woodplane follow the steps above.


# Authors

-   [Kilian Sonnentrücker](https://github.com/kilianso)
-   [Nico Martin](https://github.com/nico-martin)
-   [Mark Howells-Mead](https://github.com/markhowellsmead/)
-   [Joel Stüdle](https://github.com/joel-st)
-   [Dimitri Suter](https://github.com/gnochi/)
