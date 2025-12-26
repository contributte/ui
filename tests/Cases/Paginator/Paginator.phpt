<?php declare(strict_types = 1);

use Contributte\Tester\Toolkit;
use Contributte\UI\Paginator\ArrayDataProvider;
use Nette\Utils\Paginator;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test ArrayDataProvider basic functionality
Toolkit::test(function (): void {
	$data = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
	$provider = new ArrayDataProvider($data);

	$paginator = new Paginator();
	$paginator->setItemsPerPage(3);
	$paginator->setPage(1);

	$result = $provider->page($paginator);

	Assert::same([1, 2, 3], $result);
	Assert::same(10, $paginator->getItemCount());
});

// Test ArrayDataProvider second page
Toolkit::test(function (): void {
	$data = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
	$provider = new ArrayDataProvider($data);

	$paginator = new Paginator();
	$paginator->setItemsPerPage(3);
	$paginator->setPage(2);

	$result = $provider->page($paginator);

	Assert::same([3 => 4, 4 => 5, 5 => 6], $result);
});

// Test ArrayDataProvider last page (partial)
Toolkit::test(function (): void {
	$data = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
	$provider = new ArrayDataProvider($data);

	$paginator = new Paginator();
	$paginator->setItemsPerPage(3);
	$paginator->setPage(4);

	$result = $provider->page($paginator);

	Assert::same([9 => 10], $result);
});

// Test ArrayDataProvider with static factory
Toolkit::test(function (): void {
	$data = ['a', 'b', 'c'];
	$provider = ArrayDataProvider::create($data);

	$paginator = new Paginator();
	$paginator->setItemsPerPage(10);
	$paginator->setPage(1);

	$result = $provider->page($paginator);

	Assert::same(['a', 'b', 'c'], $result);
	Assert::same(3, $paginator->getItemCount());
});

// Test ArrayDataProvider with out-of-range page (paginator adjusts to last valid page)
Toolkit::test(function (): void {
	$data = [1, 2, 3];
	$provider = new ArrayDataProvider($data);

	$paginator = new Paginator();
	$paginator->setItemsPerPage(3);
	$paginator->setPage(5); // Paginator will adjust to page 1 (last valid page)

	$result = $provider->page($paginator);

	// Paginator adjusts out-of-range page to the last valid page
	Assert::same([1, 2, 3], $result);
	Assert::same(1, $paginator->getPage());
});
