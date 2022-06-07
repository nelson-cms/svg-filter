<?php

namespace Nelson\Latte\Filters\SvgFilter\Test;

use Nelson\Latte\Filters\SvgFilter\DI\SvgFilterConfig;
use Nelson\Latte\Filters\SvgFilter\SvgFilter;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Utils\Html;
use PHPUnit\Framework\TestCase;

class SvgFilterTest extends TestCase
{
	private static SvgFilter $svgFilter;
	private static string $assetsPath = __DIR__ . '/fixtures/';
	private static string $svgFile = 'test.svg';


	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();

		$storage = new MemoryStorage();
		$config = new SvgFilterConfig();
		$config->assetsPath = self::$assetsPath;

		static::$svgFilter = new SvgFilter($storage);
		static::$svgFilter->setup($config);
	}


	public function testSvgBasic(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'basic.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile)->toHtml();

		$this->assertSame($expected, $actual);
	}


	public function testSvgDimensions(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'dimensions.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, 50, 50)->toHtml();

		$this->assertSame($expected, $actual);
	}


	public function testSvgFill(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'fill.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, fill: 'currentColor')->toHtml();

		$this->assertSame($expected, $actual);
	}


	public function testSvgAll(): void
	{
		$expected = file_get_contents(self::$assetsPath . 'all.svg.expected');
		$actual = self::$svgFilter->inline(self::$svgFile, 50, 50, 'currentColor')->toHtml();

		$this->assertSame($expected, $actual);
	}
}
