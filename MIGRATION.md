# Woodplane Theme — Migration Guide

This documents the migration steps required when upgrading a woodplane-based theme from the legacy stack to the modern stack.

## Stack Changes Overview

| Component | Legacy | Modern |
|---|---|---|
| **PHP** | 7.x | 8.0+ (tested with 8.5) |
| **Timber** | 1.x | 2.x |
| **Twig** | 1.x/2.x | 3.x |
| **Build system** | Gulp + legacy plugins | Vite 8 / Rolldown (+ `@wordpress/scripts` for Gutenberg blocks) |
| **CSS preprocessor** | Gulp-sass | sass-embedded via Vite |
| **JS bundling** | Gulp concat/uglify | Rollup (via Vite) |
| **jQuery** | Required dependency | Removed (vanilla JS) |
| **Node.js** | Varies | 24 LTS (`.nvmrc`; `sass-embedded` needs >= 20.19) |
| **Bootstrap** | 4.x/older | 5.x |

---

## Migration Checklist

### 1. Build System: Gulp → Vite

**Remove:**
- `gulpfile.js`, `gulpfile.babel.js`, or similar
- All `gulp-*` devDependencies from `package.json`
- Any `browserSync` config

**Add:**
- `vite.config.js` (see woodplane repo for reference config)
- `postcss.config.js`
- `svgo.config.js`

**Update `package.json`:**
- Set `"type": "module"` (for ESM)
- Replace scripts:
  ```json
  "scripts": {
    "dev": "vite build --watch",
    "build": "vite build",
    "svgo": "svgo -f static/ -o static/ --config svgo.config.js"
  }
  ```
- Replace devDependencies (see woodplane `package.json` for exact versions):
  - `vite`, `sass-embedded`, `postcss`, `autoprefixer`, `cssnano`, `terser`, `svgo`

**Key Vite config details:**
- SCSS entries produce both `.css` and `.min.css` via custom `outputVariantsPlugin`
- CSS-only entries have their empty JS wrappers removed via `extractCssPlugin`
- JS entries auto-discovered from `source/scripts/*/index.js`
- No live reload: `vite build --watch` doesn't run Vite's dev server, which `vite-plugin-live-reload` would need. Reload the browser manually.
- Dynamic imports (e.g. the CitySearch's Leaflet) become chunks in `build/scripts/` with hashed names. The `.min.js` files import the `.min.js` chunks.
- Shared helpers go into a `vendor-helpers` chunk (`build.rolldownOptions.output.codeSplitting.groups`; Rolldown then also moves its CommonJS runtime into `rolldown-runtime-[hash].js`). A chunk must never import an entry, because WordPress enqueues the entry with `?ver=` and the browser would evaluate it twice. `entryImportGuardPlugin` fails the build if that happens.
- Vite 8: `build.rollupOptions` is now `build.rolldownOptions`, `manualChunks` is replaced by `codeSplitting`. Use `import.meta.dirname` instead of `__dirname` in `vite.config.js`.
- Vite 8 resolves `url()` in SCSS relative to the partial it's written in (like Sass), not relative to the entry. From `source/styles/base/_fontimport.scss` the fonts are `../../../static/fonts/...`. Unresolved paths only produce a warning ("didn't resolve at build time") and a 404 in the browser.
- svgo 4 keeps the `viewBox` by default; `removeViewBox` is no longer part of `preset-default` and must not be configured as an override.
- `Assets.php` enqueues the theme scripts as ES modules (`type="module"`). Vite resolves chunks via `import.meta.url`, a syntax error in classic scripts.
- Vite does not convert CommonJS in the theme's own source files: a `require()` ends up untouched in the bundle, and a vendored UMD file imported as a module exports nothing. Use `import` and npm packages (e.g. `fontfaceobserver`).
- Bootstrap SCSS deprecation warnings silenced via `silenceDeprecations`

**Gutenberg blocks** use `@wordpress/scripts` (webpack-based) separately from Vite, because they need WordPress's dependency extraction and JSX support. See README for setup instructions.

### 2. PHP 8.0+ Compatibility

- Add `#[\AllowDynamicProperties]` attribute to the `Theme` class (PHP 8.2+ requires this for classes using `__get`/`__set` or dynamic property assignment)
- Review all classes for deprecated PHP features
- Update `composer.json`:
  ```json
  {
    "require": {
      "php": ">=8.0",
      "timber/timber": "^2.0"
    }
  }
  ```
- Run `composer update`

### 3. Timber 1.x → 2.x

#### Template Locations
**Before (Timber 1.x):**
```php
Timber::$dirname = ['templates', 'source/views'];
```

**After (Timber 2.x):**
```php
add_filter('timber/locations', function ($paths) {
    $dirs = [
        get_template_directory() . '/templates',
        get_template_directory() . '/source/views',
    ];
    foreach ($dirs as $dir) {
        if (file_exists($dir)) {
            $paths[] = [$dir];
        }
    }
    return $paths;
});
```

#### Twig Environment
**Before:** `add_filter('timber/twig', ...)`
**After:** `add_filter('timber/twig/environment', ...)`

#### Custom Twig Functions
**Before:** `new Twig_SimpleFunction(...)`
**After:** `new \Twig\TwigFunction(...)`

#### Context
**Before:**
```php
$context = Timber::get_context();
$context['site'] = $this;
```

**After:**
```php
add_filter('timber/context', function($context) {
    // 'site' is already in context in Timber 2.x
    $context['menu'] = \Timber\Timber::get_menu('primary');
    return $context;
});
```

#### Menu
**Before:** `new TimberMenu('primary')`
**After:** `\Timber\Timber::get_menu('primary')`

#### Posts
**Before:** `new TimberPost()` or `new Timber\Post()`
**After:** `Timber::get_post()` or `Timber::get_posts()`

#### Removed Twig helpers
**Before:** `{{ function('get_current_url') }}` or `{{ post.get_comment_count }}` (any `post.get_*`)
**After:** `{{ current_url }}` (added to the context by `Theme::currentUrl()`), `{{ post.comment_count() }}`

Removed methods don't fail in Twig, they just render nothing. `function('get_current_url')` is a fatal error, unless a plugin happens to define a global `get_current_url()` (Pods' deprecated functions do), which hides it on sites with Pods.

`Theme::currentUrl()` builds the URL like WordPress' own links (`home_url()` + request path). Timber's `URLHelper::get_current_url()` repeats the port on non-standard ports (`localhost:10019:10019`), so comparisons with term links fail locally.

#### Archives
`get_queried_object()` is `null` on date archives. Guard `archive.php` before reading `->label`, `->name` etc. (`$subject->label ?? ''`, `is_object($subject) && property_exists(...)`), otherwise PHP 8 throws a fatal `TypeError` in `property_exists()`.

#### Autoloading
Timber 2.x is loaded via Composer autoload:
```php
require_once(__DIR__ . '/vendor/autoload.php');
```

### 4. jQuery → Vanilla JS

- Rewrite any jQuery-based UI code to vanilla JavaScript
- Remove jQuery from script dependencies in `Assets.php`
- The `registerAssets()` method should use `$deps = []` for scripts

### 5. Fix Assets.php — Dependency Array Bug

**File:** `base/Package/Assets.php` → `registerAdminAssets()`

This is a long-standing bug in the starter template. The admin style dependency was either:
- Wrapped in a nested array: `$deps[] = [wdpln_theme()->prefix . '-admin-editor-style'];`
  → Causes "Array to string conversion" PHP warnings
- Passed as a bare string to `wp_enqueue_style()` 3rd parameter
  → Dependency doesn't resolve correctly

**Correct pattern:**
```php
$deps = ['wp-edit-blocks'];
wp_enqueue_style(wdpln_theme()->prefix . '-admin-editor-style', ..., $deps, ...);

$deps[] = wdpln_theme()->prefix . '-admin-editor-style';
wp_enqueue_style(wdpln_theme()->prefix . '-admin-style', ..., $deps, ...);
```

`wp_enqueue_style()` and `wp_enqueue_script()` always expect `$deps` as an **array of strings**.

### 6. Bootstrap Upgrade (if applicable)

If the theme uses Bootstrap, upgrade to 5.x:
- Update `@import` paths in SCSS files
- Replace any deprecated Bootstrap 4 classes/mixins
- Add SCSS deprecation silencing in `vite.config.js`:
  ```js
  css: {
    preprocessorOptions: {
      scss: {
        silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
      },
    },
  },
  ```

### 7. SiteGround Optimizer

If the site uses SiteGround Optimizer, its JS minifier copies the theme scripts to `uploads/siteground-optimizer-assets/`, from where the relative chunk URLs don't resolve. `Assets.php` excludes the theme's scripts and styles from its minify and combine filters when the plugin is active (`defined('SiteGround_Optimizer\VERSION')`).

### 8. Plugins are optional

The theme must work when a plugin is missing or deactivated. A plugin function called from Twig (`{{ function('plugin_function') }}`, also `'Class::method'`) is a fatal error without the plugin, so guard it:

```twig
{% if function('function_exists', 'plugin_function') %}
    {{ function('plugin_function') }}
{% endif %}
```

Use `class_exists` for classes. In PHP, load plugin-specific packages only when the plugin is active (`class_exists()` / `defined()`), as `Theme.php` does for WooCommerce and Jetpack.

### 9. Fonts

- Remove FontFaceObserver (and `fonts.js`, the `fontsloaded` class): `font-display: swap` in `@font-face` does the same natively. Turn `.fontsloaded &` rules into plain rules.
- Declare bold faces with `font-weight: bold` and request them as bold, so the fallback renders bold while loading and the webfont isn't bolded synthetically.
- woff2 only; preload the files used on every page in the HTML head.
- No Google Fonts from `fonts.googleapis.com` (sends visitors' IPs to Google): use the [Fontsource](https://fontsource.org/) npm package, e.g. `@import '@fontsource/playfair-display/500';` in `_fontimport.scss`. Import the combined weight file, not single subsets: only the combined file declares `unicode-range`.

### 10. Deployment and git

- Deploy with the GitHub Actions workflow `.github/workflows/deploy.yml` (same file in all repos, see README → Deployment). It builds on GitHub, so `build/` and `vendor/` stay out of git. `DEPLOY_METHOD` lets each site choose rsync over SSH (`ssh`) or SFTP (`sftp`), each with a password or an SSH key, so the theme works with any hosting.
- Anchor `.gitignore` rules to the theme root: `/build/`, `/vendor/`, `/node_modules/`. An unanchored `vendor/` also ignores `source/scripts/ui/vendor/`; in Woodplane, `autocomplete.js` was never committed because of it, so a fresh clone couldn't build.

### 11. WP_DEBUG

During migration, you may need to temporarily disable `WP_DEBUG` in `wp-config.php` to be able to log in and update WordPress core + plugins. Re-enable it after migration is stable to catch remaining deprecation notices.

---

## Quick Checks for New Migrations

Run these from the theme root to identify issues:

```bash
# Check for the nested array deps bug
grep -n '\$deps\[\] = \[' base/Package/Assets.php

# Check for bare string deps in wp_enqueue_style
grep -n "wp_enqueue_style.*prefix.*filemtime" base/Package/Assets.php

# Check Timber version
grep timber composer.json

# Check for old Timber 1.x patterns
grep -rn "new TimberMenu\|new TimberPost\|Timber::get_context\|Timber::\\\$dirname" base/

# Check for old Twig 1.x/2.x patterns
grep -rn "Twig_SimpleFunction\|Twig_SimpleFilter" base/

# Check for Timber 1.x calls in Twig (they render nothing instead of failing)
grep -rnE "\.get_[a-z_]+|\.preview\b|function\('get_current_url'\)" source/views

# List PHP functions called from Twig: every non-core one needs a function_exists guard
grep -rhoE "(function|fn)\(\s*['\"][A-Za-z0-9_:]+['\"]" source/views | sort | uniq -c

# Source files that git ignores by accident (should print nothing except .DS_Store)
git ls-files --others --ignored --exclude-standard source

# Check for CommonJS in theme sources (Vite leaves it untouched)
grep -rn "require(" source --include='*.js'

# Check for gulpfile
ls gulpfile* 2>/dev/null

# Check for jQuery dependencies in Assets.php
grep -n "jquery" base/Package/Assets.php
```
