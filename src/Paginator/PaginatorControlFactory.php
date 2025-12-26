<?php declare(strict_types = 1);

namespace Contributte\UI\Paginator;

interface PaginatorControlFactory
{

	/**
	 * @param PaginatorDataProvider $dataProvider Data provider for pagination
	 * @param int $itemsPerPage How many items should be displayed on one page
	 * @param int $firstPage First page number
	 * @param int $relatedPages The range of pages around the current page
	 */
	public function create(
		PaginatorDataProvider $dataProvider,
		int $itemsPerPage,
		int $firstPage = 1,
		int $relatedPages = 3,
	): PaginatorControl;

}
