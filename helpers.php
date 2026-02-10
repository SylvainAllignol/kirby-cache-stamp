<?php

use Allignol\CacheStamp\CacheStamp;

if (!function_exists('cacheStamp')) {
	/**
	 * Generates a cache-stamped URL for a file
	 * 
	 * @param string $url File URL
	 * @return string Cache-stamped URL
	 */
	function cacheStamp(string $url): string
	{
		$cacheStamp = new CacheStamp();
		return $cacheStamp->stamp($url);
	}
}

if (!function_exists('cacheStampHash')) {
	/**
	 * Generates only the hash for a file
	 * 
	 * @param string $url File URL (relative or absolute)
	 * @return string|null Generated hash or null if the file doesn't exist
	 */
	function cacheStampHash(string $url): ?string
	{
		$cacheStamp = new CacheStamp();
		return $cacheStamp->getHashFromUrl($url);
	}
}
