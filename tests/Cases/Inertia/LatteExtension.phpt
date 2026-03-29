<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Inertia\Inertia;
use Contributte\UI\Inertia\LatteExtension;
use Contributte\UI\Inertia\Page;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$engine = new Engine();
	$engine->setLoader(new StringLoader());
	$engine->addExtension(new LatteExtension(new Inertia(rootId: 'spa-root')));

	$output = $engine->renderToString('{=inertia($page)}', [
		'page' => new Page('Dashboard', ['message' => 'Hello "Inertia"'], '/dashboard', 'v1'),
	]);

	Assert::contains('id="spa-root"', $output);
	Assert::contains('data-page="', $output);
	Assert::contains('&quot;component&quot;:&quot;Dashboard&quot;', $output);
	Assert::contains('&quot;message&quot;:&quot;Hello \\&quot;Inertia\\&quot;&quot;', $output);
});

Toolkit::test(function (): void {
	$engine = new Engine();
	$engine->setLoader(new StringLoader());
	$engine->addExtension(new LatteExtension(new Inertia()));

	Assert::same('', $engine->renderToString('{=inertiaHead()}'));
});
