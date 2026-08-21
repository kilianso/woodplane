import { defineConfig } from 'vite';
import path from 'path';
import fs from 'fs';
import liveReload from 'vite-plugin-live-reload';

// Discover all JS entry files in source/scripts/*/index.js
function getScriptEntries() {
	const scriptsDir = path.resolve(__dirname, 'source/scripts');
	const entries = {};
	fs.readdirSync(scriptsDir).forEach(dir => {
		const indexPath = path.resolve(scriptsDir, dir, 'index.js');
		if (fs.existsSync(indexPath)) {
			entries[`scripts/${dir}`] = indexPath;
		}
	});
	return entries;
}

/**
 * Plugin to generate both minified (.min) and unminified CSS/JS output files.
 * The PHP Asset enqueue expects both variants at build/styles/ui.css + ui.min.css etc.
 */
function outputVariantsPlugin() {
	return {
		name: 'output-variants',
		apply: 'build',
		closeBundle: {
			sequential: true,
			order: 'post',
			async handler() {
				const { default: cssnano } = await import('cssnano');
				const postcss = (await import('postcss')).default;
				const { minify } = await import('terser');
				const buildDir = path.resolve(__dirname, 'build');

				// Process CSS files: create .min.css from .css
				for (const subdir of ['styles']) {
					const dir = path.join(buildDir, subdir);
					if (!fs.existsSync(dir)) continue;
					for (const file of fs.readdirSync(dir)) {
						if (file.endsWith('.css') && !file.endsWith('.min.css')) {
							const src = path.join(dir, file);
							const dest = path.join(dir, file.replace('.css', '.min.css'));
							const css = fs.readFileSync(src, 'utf8');
							const result = await postcss([cssnano]).process(css, { from: src, to: dest });
							fs.writeFileSync(dest, result.css);
							if (result.map) {
								fs.writeFileSync(dest + '.map', result.map.toString());
							}
						}
					}
				}

				// Process JS files: create .min.js from .js
				for (const subdir of ['scripts']) {
					const dir = path.join(buildDir, subdir);
					if (!fs.existsSync(dir)) continue;
					for (const file of fs.readdirSync(dir)) {
						if (file.endsWith('.js') && !file.endsWith('.min.js')) {
							const src = path.join(dir, file);
							const dest = path.join(dir, file.replace('.js', '.min.js'));
							const code = fs.readFileSync(src, 'utf8');
							const result = await minify(code, { sourceMap: true });
							fs.writeFileSync(dest, result.code);
							if (result.map) {
								fs.writeFileSync(dest + '.map', result.map);
							}
						}
					}
				}
			},
		},
	};
}

/**
 * Plugin to extract CSS from SCSS-only entry points.
 * Vite wraps SCSS entries in a JS module — this plugin extracts
 * the actual CSS output and removes the wrapper JS files.
 */
function extractCssPlugin() {
	const cssEntryNames = ['styles/ui', 'styles/admin', 'styles/admin-editor'];

	return {
		name: 'extract-css-entries',
		apply: 'build',
		closeBundle: {
			sequential: true,
			order: 'pre',
			async handler() {
				const buildDir = path.resolve(__dirname, 'build');
				// Remove the empty JS wrapper files that Vite generates for CSS-only entries
				// and ensure CSS files exist (even if empty) since the PHP expects them
				for (const name of cssEntryNames) {
					const jsFile = path.join(buildDir, `${name}.js`);
					const mapFile = path.join(buildDir, `${name}.js.map`);
					const cssFile = path.join(buildDir, `${name}.css`);
					if (fs.existsSync(jsFile)) fs.unlinkSync(jsFile);
					if (fs.existsSync(mapFile)) fs.unlinkSync(mapFile);
					// Create empty CSS file if SCSS entry produced no output
					if (!fs.existsSync(cssFile)) {
						fs.mkdirSync(path.dirname(cssFile), { recursive: true });
						fs.writeFileSync(cssFile, '/* empty */\n');
					}
				}
			},
		},
	};
}

export default defineConfig({
	base: './',
	plugins: [
		// Live reload when PHP/Twig files change
		liveReload([
			'./**/*.php',
			'source/**/*.twig',
		]),
		extractCssPlugin(),
		outputVariantsPlugin(),
	],

	css: {
		preprocessorOptions: {
			scss: {
				// Make node_modules importable with bare specifiers (for bootstrap etc.)
				loadPaths: [path.resolve(__dirname, 'node_modules')],
				// Silence deprecation warnings from Bootstrap 5.1's legacy Sass APIs
				silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
			},
		},
	},

	build: {
		outDir: 'build',
		emptyOutDir: true,
		manifest: false,
		sourcemap: true,
		// Don't minify — we generate .min variants via the outputVariantsPlugin
		minify: false,

		rollupOptions: {
			input: {
				// SCSS entries (will produce CSS files)
				'styles/ui': path.resolve(__dirname, 'source/styles/ui.scss'),
				'styles/admin': path.resolve(__dirname, 'source/styles/admin.scss'),
				'styles/admin-editor': path.resolve(__dirname, 'source/styles/admin-editor.scss'),
				// JS entries
				...getScriptEntries(),
			},
			output: {
				entryFileNames: '[name].js',
				// Prevent code-splitting — WordPress enqueues each entry as a single file
				manualChunks: undefined,
				inlineDynamicImports: false,
				chunkFileNames: 'scripts/[name].js',
				assetFileNames: '[name][extname]',
			},
		},
	},
});
