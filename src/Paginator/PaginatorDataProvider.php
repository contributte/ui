<?php declare(strict_types = 1);

namespace Contributte\UI\Paginator;

use Countable;
use Nette\Utils\Paginator;
use Traversable;

interface PaginatorDataProvider
{

	/**
	 * @return Traversable<mixed>|Countable|array<mixed>
	 */
	public function page(Paginator $paginator): Traversable|Countable|array;

}
