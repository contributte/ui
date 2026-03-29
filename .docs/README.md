# Contributte UI

## Installation

```bash
composer require contributte/ui
```

## Usage

### Inertia

Register the extension:

```neon
extensions:
	inertia: Contributte\UI\DI\InertiaExtension

inertia:
	manifest: %wwwDir%/dist/manifest.json
	# rootId: app
	# template: %appDir%/UI/@Templates/@inertia.latte
```

Use the presenter trait:

```php
use Contributte\UI\Inertia\Presenter\TInertiaPresenter;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{
	use TInertiaPresenter;
}
```

Render an Inertia page from a presenter:

```php
final class DashboardPresenter extends BasePresenter
{
	public function renderDefault(): void
	{
		$this->inertia('Dashboard/Index', [
			'user' => ['name' => 'Felix'],
			'stats' => fn (): array => [1, 2, 3],
		]);
	}
}
```

If you want a custom root template, point the extension to your own Latte file and use the provided helpers:

```latte
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	{vitecss 'assets/js/app.ts'}
	{vitejs 'assets/js/app.ts'}
	{=inertiaHead()}
</head>
<body>
	{=inertia($page)}
</body>
</html>
```

The integration supports:

- initial HTML bootstrap with `data-page`
- JSON Inertia responses via `X-Inertia: true`
- asset version checks via `manifest`
- `409 + X-Inertia-Location` on stale assets
- partial reload filtering via `X-Inertia-Partial-*`
- session-backed validation errors via `inertiaErrors()`

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


---------------

Thanks for testing, reporting and contributing.
