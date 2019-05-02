<?php
declare(strict_types=1);

namespace Nelson\Latte\Filters\SvgFilter\DI;

use Nelson\Latte\Filters\SvgFilter\SvgFilter;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class SvgFilterExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'cacheNS' => Expect::string('inline-svg'),
			'assetsPath' => Expect::string()->required(),
		]);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();
		$builder->addDefinition($this->prefix('default'))
			->setClass(SvgFilter::class)
			->addSetup('setup', [$config]);
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		// Latte filter
		$latteFactoryName = 'latte.latteFactory';
		if ($builder->hasDefinition($latteFactoryName)) {
			/** @var FactoryDefinition $latteFactory */
			$latteFactory = $builder->getDefinition($latteFactoryName);
			$latteFactory
				->getResultDefinition()
				->addSetup('addFilter', ['svg', [$this->prefix('@default'), 'inline']]);
		}
	}
}
