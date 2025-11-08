<?php declare(strict_types = 1);

namespace Contributte\UI\DI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @method stdClass getConfig()
 */
class ViteExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'manifest' => Expect::string()->default('%wwwDir%/dist/manifest.json'),
			'base' => Expect::string()->default('/dist'),
			'nonce' => Expect::string()->nullable(),
		]);
	}

}
