<?php

namespace Nelson\Latte\Filters\SvgFilter;

use DOMDocument;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

class SvgFilter
{
	use SmartObject;


	/** @var string */
	protected $assetsPath;

	/** @var Cache */
	protected $cache;

	/** @var IStorage */
	protected $cacheStorage;


	/**
	 * @param string   $assetsPath
	 * @param IStorage $cacheStorage
	 */
	public function __construct(IStorage $cacheStorage)
	{
		$this->cacheStorage = $cacheStorage;
	}


	/**
	 * @param  array  $config
	 * @return void
	 */
	public function setup(array $config)
	{
		$this->assetsPath = $config['assetsPath'];
		$this->cache = new Cache($this->cacheStorage, $config['cacheNS']);
	}


	/**
	 * @param  string $file
	 * @return Html|void
	 */
	public function inline($file)
	{
		if (!empty($file)) {
			$filepath = $this->assetsPath . $file;

			$svg = $this->cache->call([$this, 'getSvg'], $filepath);

			if (!empty($svg)) {
				return Html::el()->setHtml($svg);
			}
		}
	}


	/**
	 * @param  string $filepath
	 * @return string
	 */
	public function getSvg($filepath)
	{
		$content = FileSystem::read($filepath);

		$doc = new DOMDocument();
		$doc->loadXML($content);
		$svgElement = $doc->getElementsByTagName('svg')->item(0);

		$doc2 = new DOMDocument();
		$doc2->appendChild($doc2->importNode($svgElement, true));

		return $doc2->saveHtml();
	}
}
