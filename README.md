![](https://heatbadger.now.sh/github/readme/contributte/ui/)

<p align=center>
  <a href="https://github.com/contributte/ui/actions"><img src="https://badgen.net/github/checks/contributte/ui/master?cache=300"></a>
  <a href="https://coveralls.io/r/contributte/ui"><img src="https://badgen.net/coveralls/c/github/contributte/ui?cache=300"></a>
  <a href="https://packagist.org/packages/contributte/ui"><img src="https://badgen.net/packagist/dm/contributte/ui"></a>
  <a href="https://packagist.org/packages/contributte/ui"><img src="https://badgen.net/packagist/v/contributte/ui"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/ui"><img src="https://badgen.net/packagist/php/contributte/ui"></a>
  <a href="https://github.com/contributte/ui"><img src="https://badgen.net/github/license/contributte/ui"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

UI helpers for Nette applications, including Vite asset tags, CSP nonce support and paginator controls.

## Versions

| State       | Version | Branch   | Nette | PHP     |
|-------------|---------|----------|-------|---------|
| dev         | `^0.3`  | `master` | 3.2+  | `>=8.2` |
| stable      | `^0.2`  | `master` | 3.1+  | `>=8.1` |

## Installation

To install latest version of `contributte/ui` use [Composer](https://getcomposer.org).

```bash
composer require contributte/ui
```

## Usage

### Bundler

```neon
services:
	latte.latteFactory:
		setup:
			- addExtension(Contributte\UI\Bundler\ViteExtension(%wwwDir%/dist/manifest.json, /dist))
```

### CSP Nonce Support

The Vite extension automatically supports Content Security Policy nonces via the `uiNonce` variable provided by [`nette/application`](https://github.com/nette/application).

```latte
{vitejs 'assets/js/app.js'}
{vitecss 'assets/js/app.js'}
```

When nonce is available, it is automatically injected:

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

### Paginator

Register the factory:

```neon
services:
	- Contributte\UI\Paginator\PaginatorControlFactory
```

Create a data provider:

```php
use Contributte\UI\Paginator\ArrayDataProvider;
use Contributte\UI\Paginator\PaginatorDataProvider;
use Nette\Utils\Paginator;

// Using built-in ArrayDataProvider
$provider = new ArrayDataProvider($data);

// Or implement your own
class MyDataProvider implements PaginatorDataProvider
{
	public function page(Paginator $paginator): array
	{
		$paginator->setItemCount($this->getTotalCount());
		return $this->fetchItems($paginator->getOffset(), $paginator->getLength());
	}
}
```

Use in presenter:

```php
use Contributte\UI\Paginator\PaginatorControlFactory;
use Contributte\UI\Paginator\ArrayDataProvider;
use Contributte\UI\Paginator\PaginatorControl;

class ArticlePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private PaginatorControlFactory $paginatorFactory,
	) {}

	protected function createComponentPaginator(): PaginatorControl
	{
		$provider = new ArrayDataProvider($this->articles);
		$control = $this->paginatorFactory->create($provider, itemsPerPage: 10);
		$control->onPagination[] = fn() => $this->redrawControl('articles');
		return $control;
	}

	public function renderDefault(): void
	{
		$this->template->articles = $this['paginator']->getPage();
	}
}
```

Render in template:

```latte
<div n:snippet="articles">
	<div n:foreach="$articles as $article">...</div>
	{control paginator}
</div>
```

Custom template:

```php
$control->setTemplateFile(__DIR__ . '/templates/myPaginator.latte');
```

Built-in templates: `bootstrap4.latte`, `bootstrap5.latte` (default).

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

## Development

See [how to contribute](https://contributte.org) to this package. This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
    <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners) **contributte** development team.
Also thank you for using this package.
