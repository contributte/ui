<?php declare(strict_types = 1);

namespace Contributte\UI\DI;

use Contributte\UI\Paginator\PaginatorControlFactory;
use Nette\DI\CompilerExtension;

class PaginatorExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addFactoryDefinition($this->prefix('factory'))
			->setImplement(PaginatorControlFactory::class);
	}

}
