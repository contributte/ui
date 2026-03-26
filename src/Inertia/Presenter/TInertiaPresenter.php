<?php declare(strict_types = 1);

namespace Contributte\UI\Inertia\Presenter;

use Contributte\UI\Inertia\ErrorStore;
use Contributte\UI\Inertia\Inertia;
use Contributte\UI\Inertia\LocationResponse;
use Nette\Http\IResponse;

trait TInertiaPresenter
{

	private Inertia $inertia;

	private ErrorStore $inertiaErrorStore;

	/** @var array<string, mixed> */
	private array $inertiaSharedProps = [];

	public function injectInertia(Inertia $inertia, ErrorStore $inertiaErrorStore): void
	{
		$this->inertia = $inertia;
		$this->inertiaErrorStore = $inertiaErrorStore;
	}

	public function redirectUrl(string $url, ?int $httpCode = null): void
	{
		if ($httpCode === null && $this->inertia->isInertiaRequest($this->getHttpRequest()) && !$this->getHttpRequest()->isMethod('GET')) {
			$httpCode = IResponse::S303_PostGet;
		}

		parent::redirectUrl($url, $httpCode);
	}

	/**
	 * @param array<string, mixed> $props
	 */
	protected function inertia(string $component, array $props = []): void
	{
		$httpRequest = $this->getHttpRequest();
		$httpResponse = $this->getHttpResponse();
		$httpResponse->setHeader('Vary', 'X-Inertia');

		if ($this->inertia->isInertiaRequest($httpRequest) && $httpRequest->isMethod('GET') && $this->inertia->isVersionMismatch($httpRequest)) {
			$this->sendResponse(new LocationResponse($httpRequest->getUrl()->getAbsoluteUrl()));
		}

		$props = array_merge(
			['errors' => $this->inertiaErrorStore->pull($httpRequest->getHeader('X-Inertia-Error-Bag'))],
			$this->getInertiaSharedProps(),
			$this->inertiaSharedProps,
			$props,
		);

		$page = $this->inertia->createPage($httpRequest, $component, $props);

		if ($this->inertia->isInertiaRequest($httpRequest)) {
			$httpResponse->setHeader('X-Inertia', 'true');
			$this->sendJson($page);
		}

		$this->setLayout(false);
		$this->template->setFile($this->inertia->getTemplateFile());
		$this->template->page = $page;
	}

	/**
	 * @param array<string, mixed> $errors
	 */
	protected function inertiaErrors(array $errors, ?string $bag = null): void
	{
		$this->inertiaErrorStore->flash($errors, $bag);
	}

	/**
	 * @param array<string, mixed>|string $key
	 */
	protected function inertiaShare(array|string $key, mixed $value = null): void
	{
		if (is_array($key)) {
			$this->inertiaSharedProps = array_merge($this->inertiaSharedProps, $key);

			return;
		}

		$this->inertiaSharedProps[$key] = $value;
	}

	protected function inertiaLocation(string $url): void
	{
		if (!$this->inertia->isInertiaRequest($this->getHttpRequest())) {
			$this->redirectUrl($url);
		}

		$this->sendResponse(new LocationResponse($url));
	}

	/**
	 * @return array<string, mixed>
	 */
	protected function getInertiaSharedProps(): array
	{
		return [];
	}

}
