<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Bundler\ViteExtension;
use Contributte\UI\Bundler\ViteProvider;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$extension = new ViteExtension(__DIR__ . '/../../Fixtures/Files/vite.manifest1.json', '/dist');

	$provider = $extension->getProviders()['vite'];

	Assert::type(ViteProvider::class, $provider);

	Assert::equal([
		'/dist/app.css',
	], $provider->css('assets/js/app.js'));

	Assert::equal([
		'/dist/app.js',
	], $provider->js('assets/js/app.js'));
});

Toolkit::test(function (): void {
	// Test rendering without nonce
	$extension = new ViteExtension(__DIR__ . '/../../Fixtures/Files/vite.manifest1.json', '/dist');
	$engine = new Engine();
	$engine->setLoader(new StringLoader());
	$engine->addExtension($extension);

	// Don't set uiNonce - it should default to undefined/null
	$template = $engine->createTemplate(
		'{vitejs "assets/js/app.js"}{vitecss "assets/js/app.js"}',
		['basePath' => '']
	);

	// Capture what would have been printed (the render method echos directly)
	ob_start();
	$template->render();
	$output = ob_get_clean();

	// Verify output without nonce
	Assert::contains('<script type="module" src="/dist/app.js"></script>', $output);
	Assert::contains('<link rel="stylesheet" href="/dist/app.css">', $output);
	Assert::notContains('nonce=', $output);
});

Toolkit::test(function (): void {
	// Test rendering with nonce
	$extension = new ViteExtension(__DIR__ . '/../../Fixtures/Files/vite.manifest1.json', '/dist');
	$engine = new Engine();
	$engine->setLoader(new StringLoader());
	$engine->addExtension($extension);

	$template = $engine->createTemplate(
		'{vitejs "assets/js/app.js"}{vitecss "assets/js/app.js"}',
		['basePath' => '']
	);

	// Set nonce on the global providers before rendering
	$template->global->uiNonce = 'abc123xyz';

	ob_start();
	$template->render();
	$output = ob_get_clean();

	// Verify output with nonce
	Assert::contains('nonce="abc123xyz"', $output);
	Assert::contains('<script type="module" src="/dist/app.js" nonce="abc123xyz"></script>', $output);
	Assert::contains('<link rel="stylesheet" href="/dist/app.css" nonce="abc123xyz">', $output);
});

Toolkit::test(function (): void {
	// Test nonce escaping with special characters
	$extension = new ViteExtension(__DIR__ . '/../../Fixtures/Files/vite.manifest1.json', '/dist');
	$engine = new Engine();
	$engine->setLoader(new StringLoader());
	$engine->addExtension($extension);

	$template = $engine->createTemplate(
		'{vitejs "assets/js/app.js"}',
		['basePath' => '']
	);

	// Set nonce with special characters
	$template->global->uiNonce = 'nonce"<>&\'';

	ob_start();
	$template->render();
	$output = ob_get_clean();

	// Verify special characters are properly escaped
	Assert::contains('nonce="nonce&quot;&lt;&gt;&amp;&#039;"', $output);
	Assert::notContains('nonce"<>&\'', $output);
});
