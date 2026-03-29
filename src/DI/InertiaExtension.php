<?php declare(strict_types = 1);

namespace Contributte\UI\DI;

use Contributte\UI\Inertia\ErrorStore;
use Contributte\UI\Inertia\Inertia;
use Contributte\UI\Inertia\LatteExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @method stdClass getConfig()
 */
final class InertiaExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'version' => Expect::string()->nullable()->dynamic()->default(null),
			'manifest' => Expect::string()->nullable()->default(null),
			'rootId' => Expect::string('app'),
			'template' => Expect::string(__DIR__ . '/../Inertia/Template/page.latte'),
			'sessionSection' => Expect::string('contributte.ui.inertia'),
		]);
	}

	public function loadConfiguration(): void
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('inertia'))
			->setFactory(Inertia::class, [
				'version' => $config->version,
				'manifest' => $config->manifest,
				'rootId' => $config->rootId,
				'templateFile' => $config->template,
			]);

		$builder->addDefinition($this->prefix('errorStore'))
			->setFactory(ErrorStore::class, [
				'section' => $config->sessionSection,
			]);

		$builder->addDefinition($this->prefix('latteExtension'))
			->setFactory(LatteExtension::class);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		if (!$builder->hasDefinition('latte.latteFactory')) {
			return;
		}

		$definition = $builder->getDefinition('latte.latteFactory');
		assert($definition instanceof FactoryDefinition);

		$definition->getResultDefinition()
			->addSetup('addExtension', [$builder->getDefinition($this->prefix('latteExtension'))]);
	}

}
