<?php
declare(strict_types=1);

namespace Nelson\Latte\Filters\SvgFilter;

use DOMDocument;
use DOMElement;
use Nelson\Latte\Filters\SvgFilter\DI\SvgFilterConfig;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

final class SvgFilter
{
	use SmartObject;

	private string $assetsPath;
	private Cache $cache;


	public function __construct(private Storage $cacheStorage)
	{
	}


	public function setup(SvgFilterConfig $config): void
	{
		$this->assetsPath = $config->assetsPath;
		$this->cache = new Cache($this->cacheStorage, $config->cacheNS);
	}


	public function inline(
		string $file,
		float $width = null,
		float $height = null,
		string $fill = null
	): ?Html {
		if (!empty($file)) {
			$filepath = $this->assetsPath . $file;

			// Has to be string because of cache
			$rawString = $this->cache->call([$this, 'getSvg'], $filepath);
			$document = $this->getDOMDocument($rawString);
			$element = $this->getSVGElement($document);

			$this->applyDimensions($element, $width, $height);
			$this->applyFill($element, $fill);

			$html = $this->saveHtml($document) ?? '';
			return Html::el()->setHtml($html);
		}

		return null;
	}


	public function getSvg(string $filepath): ?string
	{
		$content = FileSystem::read($filepath);

		$document = $this->getDOMDocument($content);
		$element = $this->getSVGElement($document);

		$result = new DOMDocument();
		$result->appendChild($result->importNode($element, true));

		return $this->saveHTML($result);
	}


	private function saveHtml(DOMDocument $document): ?string
	{
		$html = $document->saveHTML();

		if ($html === false) {
			return null;
		} else {
			return $html;
		}
	}


	private function applyDimensions(DOMElement $element, ?float $width, ?float $height): DOMElement
	{
		if (empty($width) or empty($height)) {
			return $element;
		}

		$element->setAttribute('width', (string) $width);
		$element->setAttribute('height', (string) $height);

		return $element;
	}


	private function applyFill(DOMElement $element, ?string $fill): DOMElement
	{
		if (empty($fill)) {
			return $element;
		}

		$element->setAttribute('fill', $fill);

		return $element;
	}


	private function getDOMDocument(string $svg): DOMDocument
	{
		$document = new DOMDocument();
		$document->loadXML($svg);

		return $document;
	}


	private function getSVGElement(DOMDocument $document): DOMElement
	{
		/** @var DOMElement $element */
		$element = $document->getElementsByTagName('svg')->item(0);
		return $element;
	}
}
