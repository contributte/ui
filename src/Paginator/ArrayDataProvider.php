<?php declare(strict_types = 1);

namespace Contributte\UI\Paginator;

use Countable;
use Nette\Utils\Paginator;
use Traversable;
use function array_chunk;
use function count;
use function max;

class ArrayDataProvider implements PaginatorDataProvider
{

	/**
	 * @param array<mixed> $data
	 */
	public function __construct(
		private array $data,
	)
	{
	}

	/**
	 * @param array<mixed> $data
	 */
	public static function create(array $data): self
	{
		return new self($data);
	}

	/**
	 * @return Traversable<mixed>|Countable|array<mixed>
	 */
	public function page(Paginator $paginator): Traversable|Countable|array
	{
		$paginator->setItemCount(count($this->data));
		$chunks = array_chunk($this->data, max(1, $paginator->getItemsPerPage()), true);

		return $chunks[$paginator->getPage() - $paginator->getFirstPage()] ?? [];
	}

}
