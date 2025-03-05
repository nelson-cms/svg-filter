<?php
declare(strict_types=1);

namespace NelsonCms\SvgFilter;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use NelsonCms\SvgFilter\DI\SvgFilterConfig;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Nette\Utils\Html;

final class SvgFilter
{
	use SmartObject;

	private Cache $cache;


	public function __construct(
		private readonly Storage $cacheStorage,
		private readonly SvgFilterConfig $config,
	) {
		$this->cache = new Cache($this->cacheStorage, $config->cacheNS);
	}


	/** @param array<string, string> $attributes */
	public function inline(
		string $file,
		float $width = null,
		float $height = null,
		string $fill = null,
		string $class = null,
		string $title = null,
		array $attributes = [],
	): ?Html {
		if (strlen($file) === 0) {
			return null;
		}

		$filepath = $this->config->assetsPath . $file;

		/** @var string $rawString Has to be a string because of cache */
		$rawString = $this->cache->call([$this, 'getSvg'], $filepath);
		$document = $this->getDOMDocument($rawString);
		$element = $this->getSVGElement($document);

		$this->applyDimensions($element, $width, $height);
		$this->applyFill($element, $fill);
		$this->applyClass($element, $class);
		$this->applyTitle($element, $title);
		$this->applyAttributes($element, $attributes);

		$html = $this->saveHtml($document) ?? '';
		return Html::el()->setHtml($html);
	}


	public function getSvg(string $filepath): ?string
	{
		$content = FileSystem::read($filepath);

		$document = $this->getDOMDocument($content);
		$element = $this->getSVGElement($document);

		$dom = new DOMDocument;
		$dom->appendChild($dom->importNode($element, true));

		$this->removeComments($dom);
		return $this->saveHtml($dom);
	}


	private function removeComments(DOMDocument $document): void
	{
		$xpath = new DOMXPath($document);
		$comments = $xpath->query('//comment()');

		if (!$comments instanceof DOMNodeList) {
			return;
		}

		/** @var DOMNode $comment */
		foreach ($comments as $comment) {
			$comment->parentNode?->removeChild($comment);
		}
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
		if ($width === null || $height === null) {
			return $element;
		}

		$element->setAttribute('width', (string) $width);
		$element->setAttribute('height', (string) $height);

		return $element;
	}


	private function applyFill(DOMElement $element, ?string $fill): DOMElement
	{
		if ($fill === null) {
			return $element;
		}

		$element->setAttribute('fill', $fill);

		return $element;
	}


	private function applyClass(DOMElement $element, ?string $class): DOMElement
	{
		if ($class === null) {
			return $element;
		}

		$element->setAttribute('class', $class);

		return $element;
	}


	private function applyTitle(DOMElement $element, ?string $title): DOMElement
	{
		if ($title === null) {
			return $element;
		}

		$element->setAttribute('title', $title);

		return $element;
	}


	private function getDOMDocument(string $svg): DOMDocument
	{
		$document = new DOMDocument;
		$document->loadXML($svg);

		return $document;
	}


	private function getSVGElement(DOMDocument $document): DOMElement
	{
		/** @var DOMElement $element */
		$element = $document->getElementsByTagName('svg')->item(0);
		return $element;
	}


	/** @param array<string, string> $attributes */
	private function applyAttributes(DOMElement $element, array $attributes): DOMElement
	{
		foreach ($attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}

		return $element;
	}
}
