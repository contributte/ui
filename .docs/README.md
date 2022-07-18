# Contributte UI

## Installation

```bash
composer require contributte/ui
```

## Usage

### Bundler

```neon
services:
	latte.latteFactory:
		setup:
			- addExtension(App\Model\ViteExtension(%wwwDir%/dist/manifest.json, /dist))
```

## Examples

### Vite

```js
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
	const DEV = mode === 'development';

	return {
		resolve: {
			alias: {
				'@': resolve(__dirname, 'assets/js'),
				'~': resolve(__dirname, 'node_modules'),
			},
		},
		server: {
			open: false,
			hmr: false,
		},
		build: {
			manifest: true,
			assetsDir: '',
			outDir: './www/dist/',
			emptyOutDir: false,
			minify: DEV ? false : 'esbuild',
			rollupOptions: {
				output: {
					manualChunks: undefined,
					chunkFileNames: DEV ? '[name].js' : '[name]-[hash].js',
					entryFileNames: DEV ? '[name].js' : '[name].[hash].js',
					assetFileNames: DEV ? '[name].[ext]' : '[name].[hash].[ext]',
				},
				input: {
					app: './assets/js/app.js'
				}
			}
		},
	}
});
```


---------------

Thanks for testing, reporting and contributing.
