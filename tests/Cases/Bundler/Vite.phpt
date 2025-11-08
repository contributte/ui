<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Bundler\ViteExtension;
use Contributte\UI\Bundler\ViteProvider;
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
	$extension = new ViteExtension(__DIR__ . '/../../Fixtures/Files/vite.manifest1.json', '/dist', 'abc123nonce');

	$provider = $extension->getProviders()['vite'];

	Assert::type(ViteProvider::class, $provider);
	Assert::equal('abc123nonce', $provider->getNonce());
});

Toolkit::test(function (): void {
	$extension = new ViteExtension(__DIR__ . '/../../Fixtures/Files/vite.manifest1.json', '/dist');

	$provider = $extension->getProviders()['vite'];

	Assert::null($provider->getNonce());
});
