# SVG Filter

A simple SVG filter for Nette/Latte with cache support.

## Installation

```bash
composer require nelson-cms/svg-filter
```

## Setup

```neon
extensions:
	 svgFilter: NelsonCms\SvgFilter\DI\SvgFilterExtension

svgFilter:
	assetsPath: %wwwDir%/
```

## Usage

Example:

```latte
{='assets/front/svg/logo.svg'|svg: 100, 200, 'currentColor'}
```

Parameters:

```php
float $width = null,
float $height = null,
string $fill = null,
string $class = null,
string $title = null,
array $attributes = [],
```
