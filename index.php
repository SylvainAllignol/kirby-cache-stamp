<?php

use Allignol\CacheStamp\CacheStamp;

@include_once __DIR__ . '/classes/CacheStamp.php';
@include_once __DIR__ . '/helpers.php';

Kirby::plugin('sylvainallignol/cache-stamp', [
	'components' => [
		'css' => function ($kirby, $url) {
			$cacheStamp = new CacheStamp();
			return $cacheStamp->stamp($url);
		},
		'js' => function ($kirby, $url) {
			$cacheStamp = new CacheStamp();
			return $cacheStamp->stamp($url);
		}
	],
	'fieldMethods' => [
		'cacheBust' => function ($field) {
			$cacheStamp = new CacheStamp();
			return $cacheStamp->stamp($field->value());
		}
	]
]);
