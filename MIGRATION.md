# Woodplane Theme — Migration Guide

This documents the migration steps required when upgrading a woodplane-based theme from the legacy stack to the modern stack.

## Stack Changes Overview

| Component | Legacy | Modern |
|---|---|---|
| **PHP** | 7.x | 8.0+ (tested with 8.5) |
| **Timber** | 1.x | 2.x |
| **Twig** | 1.x/2.x | 3.x |
| **Build system** | Gulp + legacy plugins | Vite 6 (+ `@wordpress/scripts` for Gutenberg blocks) |
| **CSS preprocessor** | Gulp-sass | sass-embedded via Vite |
| **JS bundling** | Gulp concat/uglify | Rollup (via Vite) |
| **jQuery** | Required dependency | Removed (vanilla JS) |
| **Node.js** | Varies | 20 LTS |
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
  - `vite`, `sass-embedded`, `postcss`, `autoprefixer`, `cssnano`, `terser`, `svgo`, `vite-plugin-live-reload`

**Key Vite config details:**
- SCSS entries produce both `.css` and `.min.css` via custom `outputVariantsPlugin`
- CSS-only entries have their empty JS wrappers removed via `extractCssPlugin`
- JS entries auto-discovered from `source/scripts/*/index.js`
- Live reload on `.php` and `.twig` file changes
- No code-splitting (WordPress enqueues each entry as a single file)
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

### 7. WP_DEBUG

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

# Check for gulpfile
ls gulpfile* 2>/dev/null

# Check for jQuery dependencies in Assets.php
grep -n "jquery" base/Package/Assets.php
```

---

## Migrated Repos

- [x] **woodplane** (starter template)
- [x] **seeland-shelties**
- [x] **ikas**
- [ ] **swissbeatbox**
