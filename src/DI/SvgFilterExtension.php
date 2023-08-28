<?php
declare(strict_types=1);

namespace Nelson\SvgFilter\DI;

use Exception;
use Nelson\SvgFilter\SvgFilter;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

final class SvgFilterExtension extends CompilerExtension
{

	/** @var SvgFilterConfig */
	public $config;


	public function getConfigSchema(): Schema
	{
		return Expect::from(new SvgFilterConfig);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();
		$builder->addDefinition($this->prefix('default'))
			->setFactory(SvgFilter::class)
			->setArgument('config', $config);
	}


	/**
	 * @throws Exception
	 */
	public function beforeCompile(): void
	{
		$latteFactory = $this->getLatteFactoryDefinition();
		$latteFactory->addSetup('addFilter', ['svg', [$this->prefix('@default'), 'inline']]);
	}


	/**
	 * @return ServiceDefinition
	 * @throws Exception
	 */
	private function getLatteFactoryDefinition(): Definition
	{
		$builder = $this->getContainerBuilder();

		$latteFactoryName = 'latte.latteFactory';

		if (!$builder->hasDefinition($latteFactoryName)) {
			throw new Exception(sprintf('Service %s not found.', $latteFactoryName));
		}

		/** @var FactoryDefinition $latteFactory */
		$latteFactory = $builder->getDefinition($latteFactoryName);
		return $latteFactory->getResultDefinition();
	}
}
