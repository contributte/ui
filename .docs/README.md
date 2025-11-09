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

### CSP Nonce Support

The Vite extension automatically supports Content Security Policy nonces via the `uiNonce` variable provided by [`nette/application`](https://github.com/nette/application).

```latte
{vitejs 'assets/js/app.js'}
{vitecss 'assets/js/app.js'}
```

When nonce is available, it's automatically injected:

```html
<script type="module" src="/dist/app.js" nonce="..."></script>
<link rel="stylesheet" href="/dist/app.css" nonce="...">
```

To use nonce support, ensure your Nette application sets the nonce value in presenter or middleware:

```php
$nonce = base64_encode(random_bytes(16));
$this->template->uiNonce = $nonce;
header("Content-Security-Policy: script-src 'nonce-{$nonce}'; style-src 'nonce-{$nonce}'");
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
