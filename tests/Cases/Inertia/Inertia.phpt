<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Inertia\Inertia;
use Contributte\UI\Inertia\Page;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$inertia = new Inertia(version: 'build-123', rootId: 'frontend', templateFile: '/tmp/root.latte');
	$request = new Request(new UrlScript('https://example.com/articles?foo=1', '/index.php'));

	$page = $inertia->createPage($request, 'Articles/Index', ['articles' => ['a', 'b']]);

	Assert::type(Page::class, $page);
	Assert::same('frontend', $inertia->getRootId());
	Assert::same('/tmp/root.latte', $inertia->getTemplateFile());
	Assert::same('build-123', $page->version);
	Assert::same('/articles?foo=1', $page->url);
	Assert::same('Articles/Index', $page->component);
	Assert::same(['articles' => ['a', 'b']], $page->props);
	Assert::same([
		'component' => 'Articles/Index',
		'props' => ['articles' => ['a', 'b']],
		'url' => '/articles?foo=1',
		'version' => 'build-123',
		'clearHistory' => false,
		'encryptHistory' => false,
	], $page->toArray());
});

Toolkit::test(function (): void {
	$state = (object) ['counter' => 0];
	$inertia = new Inertia(version: 'v1');
	$request = new Request(
		new UrlScript('https://example.com/users', '/index.php'),
		headers: [
			'X-Inertia-Partial-Component' => 'Users/Index',
			'X-Inertia-Partial-Data' => 'users',
		],
	);

	$page = $inertia->createPage($request, 'Users/Index', [
		'users' => function () use ($state): array {
			$state->counter++;

			return ['Felix'];
		},
		'companies' => function () use ($state): array {
			$state->counter += 100;

			return ['Contributte'];
		},
		'errors' => ['name' => 'Required'],
	]);

	Assert::same(1, $state->counter);
	Assert::same([
		'users' => ['Felix'],
		'errors' => ['name' => 'Required'],
	], $page->props);
});

Toolkit::test(function (): void {
	$inertia = new Inertia(version: 'v1');
	$request = new Request(
		new UrlScript('https://example.com/users', '/index.php'),
		headers: [
			'X-Inertia-Partial-Component' => 'Users/Index',
			'X-Inertia-Partial-Except' => 'filters',
		],
	);

	$page = $inertia->createPage($request, 'Users/Index', [
		'users' => ['Felix'],
		'filters' => ['active' => true],
		'errors' => ['name' => 'Required'],
	]);

	Assert::same([
		'users' => ['Felix'],
		'errors' => ['name' => 'Required'],
	], $page->props);
});

Toolkit::test(function (): void {
	$inertia = new Inertia(version: 'v1');
	$request = new Request(new UrlScript('https://example.com', '/index.php'), headers: ['X-Inertia-Version' => 'old']);

	Assert::true($inertia->isVersionMismatch($request));
	Assert::false($inertia->isVersionMismatch(new Request(new UrlScript('https://example.com', '/index.php'), headers: ['X-Inertia-Version' => 'v1'])));
	Assert::true($inertia->isInertiaRequest(new Request(new UrlScript('https://example.com', '/index.php'), headers: ['X-Inertia' => 'true'])));
});
