<?php declare(strict_types = 1);

namespace Contributte\UI\Paginator;

interface PaginatorControlFactory
{

	public function create(
		PaginatorDataProvider $dataProvider,
		int $itemsPerPage,
		int $firstPage = 1,
		int $relatedPages = 3,
	): PaginatorControl;

}
