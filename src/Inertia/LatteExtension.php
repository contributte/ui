<?php declare(strict_types = 1);

namespace Contributte\UI\Inertia;

use Latte\Extension;
use Latte\Runtime\Html;
use Nette\Utils\Json;

final class LatteExtension extends Extension
{

	public function __construct(
		private readonly Inertia $inertia,
	)
	{
	}

	/**
	 * @return array<string, callable>
	 */
	public function getFunctions(): array
	{
		return [
			'inertia' => fn (Page $page): Html => $this->renderRoot($page),
			'inertiaHead' => static fn (): Html => new Html(''),
		];
	}

	private function renderRoot(Page $page): Html
	{
		$payload = htmlspecialchars(Json::encode($page->toArray()), ENT_QUOTES, 'UTF-8');

		return new Html(sprintf('<div id="%s" data-page="%s"></div>', htmlspecialchars($this->inertia->getRootId(), ENT_QUOTES, 'UTF-8'), $payload));
	}

}
