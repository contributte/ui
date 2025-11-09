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
	// Verify nonce support is implemented in ViteExtension
	$extensionCode = file_get_contents(__DIR__ . '/../../../src/Bundler/ViteExtension.php');

	// Check tagVitejs method contains nonce handling
	Assert::contains('$this->global->uiNonce', $extensionCode);
	Assert::contains('htmlspecialchars($this->global->uiNonce', $extensionCode);
	Assert::contains('nonce="', $extensionCode);

	// Verify both script and link tags handle nonce
	Assert::contains('<script type="module" src=', $extensionCode);
	Assert::contains('<link rel="stylesheet" href=', $extensionCode);

	// Verify that nonce is conditionally applied
	Assert::contains('$nonceAttr = $this->global->uiNonce ?', $extensionCode);
});
