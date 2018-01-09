<?php

namespace Nelson\Latte\Filters\SvgFilter\DI;

use Latte\Engine;
use Nelson\Latte\Filters\SvgFilter\SvgFilter;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;

final class SvgFilterExtension extends CompilerExtension
{

	/** @var array */
	protected $defaults = [
		'cacheNS' => 'inline-svg',
		'assetsPath' => '%project.paths.assetsDir%',
	];


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		$builder->addDefinition($this->prefix('default'))
			->setClass(SvgFilter::class)
			->addSetup('setup', [$config]);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$registerToLatte = function (ServiceDefinition $def) {
			$def->addSetup('addFilter', ['svg', [$this->prefix('@default'), 'inline']]);
		};

		$latteFactoryService = $builder->getByType(ILatteFactory::class);
		if (!$latteFactoryService || !self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), Engine::class)) {
			$latteFactoryService = 'nette.latteFactory';
		}

		if ($builder->hasDefinition($latteFactoryService) && self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), Engine::class)) {
			$registerToLatte($builder->getDefinition($latteFactoryService));
		}

		if ($builder->hasDefinition('nette.latte')) {
			$registerToLatte($builder->getDefinition('nette.latte'));
		}
	}


	/**
	 * @param string $class
	 * @param string $type
	 * @return bool
	 */
	private static function isOfType($class, $type)
	{
		return $class === $type || is_subclass_of($class, $type);
	}
}
