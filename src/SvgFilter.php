<?php

namespace Nelson\Latte\Filters\SvgFilter;

use DOMElement;
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
	public function inline($file, $width = null, $height = null, $fill = null)
	{
		if (!empty($file)) {
			$filepath = $this->assetsPath . $file;

			// Has to be string because of cache
			$rawString = $this->cache->call([$this, 'getSvg'], $filepath);
			$document = $this->getDOMDocument($rawString);
			$element = $this->getSVGElement($document);

			$element = $this->applyDimensions($element, $width, $height);
			$element = $this->applyFill($element, $fill);

			if (!empty($document)) {
				return Html::el()->setHtml($document->saveHTML());
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

		$document = $this->getDOMDocument($content);
		$element = $this->getSVGElement($document);

		$result = new DOMDocument();
		$result->appendChild($result->importNode($element, true));

		return $result->saveHTML();
	}


	/**
	 * @param  DOMElement $element
	 * @param  string     $width
	 * @param  string     $height
	 * @return DOMElement
	 */
	protected function applyDimensions(DOMElement $element, $width, $height)
	{
		if (empty($width) OR empty($height)) {
			return $element;
		}

		$element->setAttribute('width', $width);
		$element->setAttribute('height', $height);

		return $element;
	}


	/**
	 * @param  DOMElement $element
	 * @param  string     $fill
	 * @return DOMElement
	 */
	protected function applyFill(DOMElement $element, $fill)
	{
		if (empty($fill)) {
			return $element;
		}

		$element->setAttribute('fill', $fill);

		return $element;
	}


	/**
	 * @param  string $svg
	 * @return DOMDocument
	 */
	protected function getDOMDocument($svg)
	{
		$document = new DOMDocument();
		$document->loadXML($svg);

		return $document;
	}


	/**
	 * @param  DOMDocument $document
	 * @return DOMElement
	 */
	protected function getSVGElement(DOMDocument $document)
	{
		return $document->getElementsByTagName('svg')->item(0);
	}
}
