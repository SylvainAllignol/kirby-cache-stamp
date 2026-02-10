<?php

namespace Allignol\CacheStamp;

use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\F;
use Kirby\Cms\Url;

class CacheStamp
{
	/**
	 * Allowed extensions for cache busting
	 */
	const ALLOWED_EXTENSIONS = [
		'css',
		'js',
		'woff',
		'woff2',
		'ttf',
		'svg',
		'pdf',
		'jpg',
		'jpeg',
		'png',
		'gif',
		'webp',
		'avif'
	];

	protected Kirby $kirby;
	protected array $options;

	public function __construct()
	{
		$defaults = [
			'active' => true,
			'method' => 'xxh3',
			'prefix' => '',
			'suffix' => ''
		];

		$options = (array) kirby()->option('allignol.cache-stamp', []);

		$this->options = array_merge($defaults, $options);

		$this->options['prefix'] = $this->sanitizeAffix($this->options['prefix']);
		$this->options['suffix'] = $this->sanitizeAffix($this->options['suffix']);
	}

	/**
	 * Sanitizes a prefix or suffix (allows only a-z, A-Z, 0-9, - and _)
	 */
	protected function sanitizeAffix(string $affix): string
	{
		return preg_replace('/[^a-zA-Z0-9_-]/', '', $affix);
	}

	/**
	 * Generates the hash for a file from a file system path
	 * 
	 * @param string $file File system path
	 * @return string|null Generated hash or null if the file doesn't exist or if its modification time cannot be determined
	 */
	public function generateHash(string $file): ?string
	{
		if (!F::exists($file)) {
			return null;
		}

		$timestamp = F::modified($file);
		if ($timestamp === false) {
			return null;
		}

		return match ($this->options['method']) {
			'timestamp' => (string) $timestamp,
			'md5', 'sha1', 'sha256', 'xxh3'
			=> hash($this->options['method'], (string) $timestamp),
			default => null,
		};
	}

	/**
	 * Generates a cache-stamped URL for a given file URL
	 * 
	 * @param string $url URL (relative or absolute)
	 * @return string Cache-stamped URL or original URL if it cannot be processed
	 */
	public function stamp(string $url): string
	{
		if ($this->options['active'] !== true) {
			return $url;
		}

		// Ignore query strings
		if (str_contains($url, '?')) {
			return $url;
		}

		$isAbsolute = Url::isAbsolute($url, kirby()->url());

		// Ignore external URLs
		if($isAbsolute && !str_starts_with($url, kirby()->url())) {
			return $url;
		}

		$file = Url::path($url, false);
		$extension = F::extension($file);

		// If the extension is not in the allowed list, return the original URL
		if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
			return $url;
		}

		$hash = $this->generateHash($file);
		if (!$hash) {
			return $url;
		}

		$hash = $this->options['prefix'] . $hash . $this->options['suffix'];

		$dirname  = F::dirname($file);
		$filename = F::name($file);

		$stamped = ($dirname === '.')
			? $filename . '.' . $hash . '.' . $extension
			: $dirname . '/' . $filename . '.' . $hash . '.' . $extension;

		// If the original URL was absolute, return an absolute URL; otherwise, return a relative URL
		return $isAbsolute
			? kirby()->url() . '/' . $stamped
			: '/' . $stamped;
	}

	/**
	 * Extracts the hash from a URL
	 * 
	 * @param string $url URL (relative or absolute)
	 * @return string|null Generated hash or null if the file doesn't exist or if the URL is invalid
	 */
	public function getHashFromUrl(string $url): ?string
	{
		$file = Url::path($url, false);
		return $this->generateHash($file);
	}

	/**
	 * Returns the current options
	 * 
	 * @return array Current options
	 */
	public function options(): array
	{
		return $this->options;
	}

	/**
	 * Checks if the plugin is active
	 * 
	 * @return bool True if active, false otherwise
	 */
	public function isActive(): bool
	{
		return $this->options['active'];
	}
}
