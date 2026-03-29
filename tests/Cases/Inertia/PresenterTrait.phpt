<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Inertia\ErrorStore;
use Contributte\UI\Inertia\Inertia;
use Contributte\UI\Inertia\LocationResponse;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Http\Request;
use Nette\Http\Session;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tests\Fixtures\Inertia\TestHttpResponse;
use Tests\Fixtures\Inertia\TestPresenter;

require_once __DIR__ . '/../../bootstrap.php';

function prepareSessionEnvironment(): void
{
	if (session_status() === PHP_SESSION_ACTIVE) {
		session_write_close();
	}

	session_id('');
	session_name('ui_inertia_' . uniqid());
}

/**
 * @return array{0: TestPresenter, 1: TestHttpResponse, 2: Request, 3: Session}
 */
function createPresenter(Request $request, Inertia $inertia): array
{
	$response = new TestHttpResponse();
	$session = new Session($request, $response);
	$presenter = new TestPresenter();
	$presenter->injectPrimary($request, $response, null, null, $session);
	$presenter->injectInertia($inertia, new ErrorStore($session, 'tests.inertia.presenter'));

	return [$presenter, $response, $request, $session];
}

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$request = new Request(
		new UrlScript('https://example.com/dashboard?tab=users', '/index.php'),
		headers: [
			'X-Inertia' => 'true',
			'X-Inertia-Version' => 'v1',
		],
	);

	[$presenter, $response, , $session] = createPresenter($request, new Inertia(version: 'v1'));
	$presenter->setSharedProps(['shared' => 'yes']);
	$presenter->shareInertia('auth', ['user' => 'Felix']);

	Assert::exception(fn () => $presenter->runInertia('Dashboard', ['stats' => fn (): array => [1, 2, 3]]), AbortException::class);

	$captured = $presenter->getCapturedResponse();
	Assert::type(JsonResponse::class, $captured);
	assert($captured instanceof JsonResponse);
	Assert::same('X-Inertia', $response->getHeader('Vary'));
	Assert::same('true', $response->getHeader('X-Inertia'));

	$payload = $captured->getPayload();
	Assert::same('Dashboard', $payload->component);
	Assert::same('/dashboard?tab=users', $payload->url);
	Assert::same('v1', $payload->version);
	Assert::same([
		'errors' => [],
		'shared' => 'yes',
		'auth' => ['user' => 'Felix'],
		'stats' => [1, 2, 3],
	], $payload->props);

	$session->close();
	session_id('');
});

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$request = new Request(new UrlScript('https://example.com/home', '/index.php'));

	[$presenter, , , $session] = createPresenter($request, new Inertia(version: 'v1'));

	Assert::exception(fn () => $presenter->triggerLocation('/login'), AbortException::class);

	$captured = $presenter->getCapturedResponse();
	Assert::type(RedirectResponse::class, $captured);
	assert($captured instanceof RedirectResponse);
	Assert::same('/login', $captured->getUrl());
	Assert::same(302, $captured->getCode());

	$session->close();
	session_id('');
});

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$request = new Request(
		new UrlScript('https://example.com/users', '/index.php'),
		headers: [
			'X-Inertia' => 'true',
			'X-Inertia-Version' => 'old',
		],
	);

	[$presenter, $response, $httpRequest, $session] = createPresenter($request, new Inertia(version: 'v2'));

	Assert::exception(fn () => $presenter->runInertia('Users/Index'), AbortException::class);

	$captured = $presenter->getCapturedResponse();
	Assert::type(LocationResponse::class, $captured);
	assert($captured instanceof LocationResponse);

	$captured->send($httpRequest, $response);
	Assert::same(409, $response->getCode());
	Assert::same('https://example.com/users', $response->getHeader('X-Inertia-Location'));

	$session->close();
	session_id('');
});

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$request = new Request(
		new UrlScript('https://example.com/form', '/index.php'),
		post: ['name' => ''],
		headers: ['X-Inertia' => 'true'],
		method: 'POST',
	);

	[$presenter, , , $session] = createPresenter($request, new Inertia(version: 'v1'));

	Assert::exception(fn () => $presenter->triggerRedirect('/form'), AbortException::class);

	$captured = $presenter->getCapturedResponse();
	Assert::type(RedirectResponse::class, $captured);
	assert($captured instanceof RedirectResponse);

	Assert::same('/form', $captured->getUrl());
	Assert::same(303, $captured->getCode());

	$session->close();
	session_id('');
});

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$request = new Request(
		new UrlScript('https://example.com/form', '/index.php'),
		headers: [
			'X-Inertia' => 'true',
			'X-Inertia-Version' => 'v1',
			'X-Inertia-Error-Bag' => 'createUser',
		],
	);

	[$presenter, , , $session] = createPresenter($request, new Inertia(version: 'v1'));
	$presenter->flashInertiaErrors(['email' => 'Required']);

	Assert::exception(fn () => $presenter->runInertia('Users/Create'), AbortException::class);

	$captured = $presenter->getCapturedResponse();
	Assert::type(JsonResponse::class, $captured);
	assert($captured instanceof JsonResponse);
	$payload = $captured->getPayload();
	Assert::same(['createUser' => ['email' => 'Required']], $payload->props['errors']);

	$session->close();
	session_id('');
});
