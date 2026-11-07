# Fotorama for Datenstrom Yellow

Responsive image galleries for the current Datenstrom Yellow 1.x release, powered by [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama).

## Requirements

- Current Datenstrom Yellow 1.x.
- Yellow Image extension enabled.
- PHP and image processing requirements supported by your Yellow installation.

The extension bundles jQuery 3.7.1 locally. It does not depend on a CDN or a theme-provided jQuery copy.

## Installation

1. Download the repository as a ZIP (or download the release archive).
2. Copy the extension files into Yellow's extension directory, normally `system/extensions/`:
   - `fotorama.php`
   - `fotorama.js`
   - `fotorama.css`
   - `jquery.min.js`
   - `fotorama.png` and `fotorama@2x.png`
3. Keep the extension files together in the same directory. Do not put them in `system/themes/` or `media/`.
4. Ensure the Yellow Image extension is installed and enabled.
5. Open a page containing the shortcode and regenerate/reload the site if your installation caches generated output.

The extension loads the CSS, jQuery and Fotorama scripts only on pages whose source contains a Fotorama shortcode. All asset URLs are generated from Yellow's configured server base and extension location.

### Update

Replace the existing Fotorama files with the new versions. Preserve your page content and gallery shortcuts. No database migration or configuration entry is required.

### Uninstall

Remove `fotorama.php`, `fotorama.js`, `fotorama.css`, `jquery.min.js`, `fotorama.png` and `fotorama@2x.png` from the extension directory. Gallery shortcuts will no longer be rendered.

## Usage

Add a shortcode to a Yellow page:

```text
[fotorama image-.*.jpg]
```

The first argument is a regular expression matched against filenames in Yellow's configured image directory. The optional second argument sets the thumbnail dimensions; the default is `100%`.

Examples:

```text
[fotorama image-.*]
[fotorama vacation-.*\\.jpg 800]
```

Images are selected from Yellow's media index and passed through the Yellow Image extension, which generates thumbnails as needed. The gallery uses Fotorama's fullscreen support and `cover` fitting.

## Included libraries

- [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama) — jQuery image gallery.
- [jQuery 3.7.1](https://jquery.com/) — bundled locally under its MIT license.
