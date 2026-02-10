# Kirby Cache Stamp

A simple and lightweight Kirby plugin to handle **cache busting for CSS, JS, fonts, SVG, PDF and images** with advanced configuration options.

## Features

- Automatic cache busting for CSS, JS, fonts, SVG, PDF and images
- Multiple hash methods (MD5, SHA1, SHA256, XXH3, Timestamp)
- Customizable hash prefix and suffix
- Helpers and field methods
- Supports both relative and absolute URLs

## Requirements

- Kirby CMS: 3x, 4.x, or 5.x
- PHP 8.2 or superior

## Installation

### Via Composer (recommended)

```bash
composer require allignol/cache-stamp
```

### Manual (ZIP)

1. Download the latest release ZIP from GitHub
2. Unzip into `site/plugins/kirby-cache-stamp`

### Apache (.htaccess)

Add the following rules to the .htaccess file at the root of your site:

```bash
# --------------------------------------------------------
# Cache busting for CSS, JS, fonts, SVG, PDF and images
# --------------------------------------------------------

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)\.([a-zA-Z0-9]+)\.(css|js|woff2?|ttf|svg|pdf|jpe?g|png|gif|webp|avif)$ $1.$3 [L,NC]
```

### Nginx (example)

```nginx
location ~* ^/(.+)\.[a-zA-Z0-9]+\.(css|js|woff2?|ttf|svg|pdf|jpe?g|png|gif|webp|avif)$ {
    try_files /$1.$2 $uri =404;
}
```

## Configuration

Add options to your `config.php`:

```php
return [
    'allignol.cache-stamp' => [
        'active' => true,          // Enable/disable the plugin
        'method' => 'xxh3',        // Hash method: md5, sha1, sha256, xxh3, timestamp
        'prefix' => '',            // Prefix before the hash (a-z, A-Z, 0-9, -, _)
        'suffix' => ''             // Suffix after the hash (a-z, A-Z, 0-9, -, _)
    ]
];
```

### Available Hash Methods

All hash methods use the file's **modification timestamp** as their source. The hash obscures the actual modification date for security reasons while still ensuring the URL changes when the file is updated.

- **xxh3** (default): Fast and efficient hash algorithm - produces a 16-character hash from the timestamp
- **md5**: MD5 hash - produces a 32-character hash from the timestamp
- **sha1**: SHA1 hash - produces a 40-character hash from the timestamp
- **sha256**: SHA256 hash - produces a 64-character hash from the timestamp
- **timestamp**: Direct file modification timestamp - uses the raw timestamp (not recommended for production as it exposes modification dates)

**Note:** If the file doesn't exist or its modification time cannot be determined, the plugin returns the original URL unchanged (no cache busting applied).


### Supported File Types

The plugin automatically processes these file types:
- **css** - Stylesheets
- **js** - JavaScript files
- **woff, woff2** - Web fonts
- **ttf** - TrueType fonts
- **svg** - SVG files
- **pdf** - PDF documents
- **jpg, jpeg** - JPEG images
- **png** - PNG images
- **gif** - GIF images
- **webp** - WebP images
- **avif** - AVIF images

## Usage

### Automatic (Components)

The plugin automatically handles `css()` and `js()` helpers:

```php
<?= css('assets/css/style.css') ?>
// Output: <link href="assets/css/style.a1b2c3d4.css" rel="stylesheet">

<?= js('assets/js/script.js') ?>
// Output: <script src="assets/js/script.e5f6g7h8.js"></script>
```

### Helpers

Use the provided helper functions in your templates:

```php
<?= cacheStamp('assets/css/style.css') ?>
// Output: assets/css/style.a1b2c3d4.css

<?= cacheStamp('assets/js/script.js') ?>
// Output: assets/js/script.e5f6g7h8.js

// Works with absolute URLs too (e.g., from asset()->url())
<?= cacheStamp(asset('images/logo.svg')->url()) ?>
// Output: https://yoursite.com/assets/images/logo.a1b2c3d4.svg

// Get only the hash
<?= cacheStampHash('assets/css/style.css') ?>
// Output: a1b2c3d4
```

### Field Methods

Use cache stamp on field values when you store file paths in custom fields.

**Useful when:**
- Users can configure custom stylesheets or scripts via the panel
- You have flexible templates with dynamic asset loading
- Custom icons, fonts, or documents are stored in text fields

**Example blueprint:**
```yaml
fields:
  customStylesheet:
    type: text
    label: Custom Stylesheet
    placeholder: assets/css/custom.css
  brochurePdf:
    type: text
    label: Product Brochure
    placeholder: documents/brochure.pdf
```

**Usage in templates:**
```php
// Custom stylesheet
<?php if ($url = $page->customStylesheet()->cacheStamp()): ?>
    <link href="<?= $url ?>" rel="stylesheet">
<?php endif ?>

// PDF download link
<?php if ($url = $page->brochurePdf()->cacheStamp()): ?>
    <a href="<?= $url ?>">Download brochure</a>
<?php endif ?>
```

### Using the Class Directly

For advanced usage, you can use the `CacheStamp` class directly:

```php
use Allignol\CacheStamp\CacheStamp;

$cacheStamp = new CacheStamp();

// Get stamped URL
$url = $cacheStamp->stamp('assets/css/style.css');

// Check if active
if ($cacheStamp->isActive()) {
    // ...
}

// Get current method
$method = $cacheStamp->method();

// Get all options
$options = $cacheStamp->options();
```

## Example

```php
// config.php
return [
    'allignol.cache-stamp' => [
        'method' => 'md5',
        'prefix' => 'v-',
        'suffix' => '-cached'
    ]
];
```

Output: `style.v-d41d8cd98f00b204e9800998ecf8427e-cached.css`

## Notes

- Absolute URLs remain absolute; relative URLs remain relative
- URLs with query strings (?v=123) are ignored
- Recommended for production: xxh3 or md5 for security & performance

## Troubleshooting

- No cache busting? Check that your rewrite rules are active and the plugin is enabled.
- URL unchanged? The file may not exist or its extension is not supported.
- Using query strings ? Those URLs are intentionally left untouched.

## License

[MIT](https://opensource.org/licenses/MIT)

## Credits

- [Sylvain Allignol](https://sylvainallignol.com)