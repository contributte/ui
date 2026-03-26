<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Inertia\ErrorStore;
use Nette\Http\Request;
use Nette\Http\Session;
use Nette\Http\UrlScript;
use Tester\Assert;
use Tests\Fixtures\Inertia\TestHttpResponse;

require_once __DIR__ . '/../../bootstrap.php';

function prepareSessionEnvironment(): void
{
	if (session_status() === PHP_SESSION_ACTIVE) {
		session_write_close();
	}

	session_id('');
	session_name('ui_inertia_' . uniqid());
}

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$session = new Session(new Request(new UrlScript('https://example.com', '/index.php')), new TestHttpResponse());
	$errorStore = new ErrorStore($session, 'tests.inertia');

	$errorStore->flash(['email' => 'Required']);
	Assert::same(['email' => 'Required'], $errorStore->pull());
	Assert::same([], $errorStore->pull());

	if ($session->isStarted()) {

		$session->destroy();
	}

	$session->close();
	session_id('');
});

Toolkit::test(function (): void {
	prepareSessionEnvironment();

	$session = new Session(new Request(new UrlScript('https://example.com', '/index.php')), new TestHttpResponse());
	$errorStore = new ErrorStore($session, 'tests.inertia');

	$errorStore->flash(['email' => 'Required']);
	Assert::same(['createUser' => ['email' => 'Required']], $errorStore->pull('createUser'));

	if ($session->isStarted()) {

		$session->destroy();
	}

	$session->close();
	session_id('');
});
