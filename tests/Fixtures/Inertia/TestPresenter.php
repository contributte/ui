<?php declare(strict_types = 1);

namespace Tests\Fixtures\Inertia;

use Contributte\UI\Inertia\Presenter\TInertiaPresenter;
use Nette\Application\AbortException;
use Nette\Application\Response;
use Nette\Application\UI\Presenter;

final class TestPresenter extends Presenter
{

	use TInertiaPresenter;

	private ?Response $capturedResponse = null;

	/** @var array<string, mixed> */
	private array $sharedProps = [];

	/**
	 * @param array<string, mixed> $props
	 */
	public function runInertia(string $component, array $props = []): void
	{
		$this->inertia($component, $props);
	}

	/**
	 * @param array<string, mixed>|string $key
	 */
	public function shareInertia(array|string $key, mixed $value = null): void
	{
		$this->inertiaShare($key, $value);
	}

	/**
	 * @param array<string, mixed> $errors
	 */
	public function flashInertiaErrors(array $errors, ?string $bag = null): void
	{
		$this->inertiaErrors($errors, $bag);
	}

	public function triggerRedirect(string $url): void
	{
		$this->redirectUrl($url);
	}

	public function triggerLocation(string $url): void
	{
		$this->inertiaLocation($url);
	}

	public function getCapturedResponse(): ?Response
	{
		return $this->capturedResponse;
	}

	/**
	 * @param array<string, mixed> $props
	 */
	public function setSharedProps(array $props): void
	{
		$this->sharedProps = $props;
	}

	public function sendResponse(Response $response): void
	{
		$this->capturedResponse = $response;

		throw new AbortException();
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function getInertiaSharedProps(): array
	{
		return $this->sharedProps;
	}

}
