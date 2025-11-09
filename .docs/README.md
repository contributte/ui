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

### Content Security Policy (CSP) Nonce Support

The Vite extension automatically supports Content Security Policy (CSP) nonces. This allows you to enforce strict CSP policies while still loading scripts and stylesheets with Vite.

The nonce value is automatically injected into `<script>` and `<link>` tags when available through the `uiNonce` global variable in Latte templates.

#### Usage in Latte Templates

```latte
{vitejs 'assets/js/app.js'}
{vitecss 'assets/js/app.js'}
```

When a nonce is set via the Nette application context, it will be automatically added to the generated tags:

```html
<script type="module" src="/dist/app.js" nonce="..."></script>
<link rel="stylesheet" href="/dist/app.css" nonce="...">
```

#### Setting the Nonce in Your Application

The nonce should be generated per-request and made available to templates through Nette's presenter or Latte engine. Here's an example:

```php
// In your BasePresenter or middleware
public function beforeRender(): void
{
    // Generate a nonce for this request
    $nonce = base64_encode(random_bytes(16));

    // Make it available to templates
    $this->template->uiNonce = $nonce;

    // Add it to your CSP header
    header("Content-Security-Policy: script-src 'nonce-{$nonce}'; style-src 'nonce-{$nonce}'");
}
```

#### Security

- The nonce is properly escaped using `htmlspecialchars()` to prevent XSS vulnerabilities
- Special characters in nonce values are safely encoded
- The nonce attribute is only added when a nonce value is present

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
