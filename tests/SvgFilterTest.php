<?php
declare(strict_types=1);

namespace Nelson\SvgFilter\Tests;

use Nelson\SvgFilter\DI\SvgFilterConfig;
use Nelson\SvgFilter\SvgFilter;
use Nette\Caching\Storages\MemoryStorage;
use PHPUnit\Framework\TestCase;

class SvgFilterTest extends TestCase
{
	private static SvgFilter $svgFilter;
	private static string $assetsPath = __DIR__ . '/fixtures/';
	private static string $svgFile = 'test.svg';


	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		$storage = new MemoryStorage;
		$config = new SvgFilterConfig;
		$config->assetsPath = self::$assetsPath;

		self::$svgFilter = new SvgFilter($storage, $config);
	}


	public function testSvgBasic(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'basic.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile)?->toHtml();

		self::assertSame($expected, $actual);
	}


	public function testSvgDimensions(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'dimensions.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, 50, 50)?->toHtml();

		self::assertSame($expected, $actual);
	}


	public function testSvgFill(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'fill.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, fill: 'currentColor')?->toHtml();

		self::assertSame($expected, $actual);
	}


	public function testSvgClass(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'class.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, class: 'big')?->toHtml();

		self::assertSame($expected, $actual);
	}


	public function testSvgTitle(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'title.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, title: 'svg')?->toHtml();

		self::assertSame($expected, $actual);
	}


	public function testSvgAll(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'all.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, 50, 50, 'currentColor', 'big', 'svg')?->toHtml();

		self::assertSame($expected, $actual);
	}
}
