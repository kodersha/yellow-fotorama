# Fotorama for Datenstrom Yellow

Responsive image galleries powered by [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama).

This extension is updated for **Datenstrom Yellow 1.0** and keeps the original `[fotorama]` content shortcut.

## Requirements

- Datenstrom Yellow 1.0.
- The Yellow Image extension.
- PHP and server requirements supported by your Yellow installation.

jQuery 3.7.1 is included locally. No external CDN or separately installed jQuery library is required.

## Installation

1. Download the latest release archive.
2. Install the extension in your Yellow `system` directory using the usual Yellow extension installation procedure.
3. Make sure the Yellow Image extension is installed and enabled.

To uninstall, remove the Fotorama extension files, including `fotorama.php`, `fotorama.js`, `fotorama.css`, `fotorama.png`, `fotorama@2x.png`, and `jquery.min.js`.

## Usage

Add a Fotorama shortcut to a page:

```text
[fotorama image-.*.jpg]
```

The first argument is a regular expression matched against filenames in the configured Yellow image directory. The optional second argument sets the maximum thumbnail dimensions; the default is `100%` (original image dimensions).

Examples:

```text
[fotorama image-.*]
[fotorama vacation-.*\.jpg 800]
```

The gallery assets are added to the page header only when a valid Fotorama shortcut is present. Images are processed through Yellow's Image extension, which creates thumbnails when needed.

## Included libraries

- [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama) — jQuery image gallery.
- [jQuery 3.7.1](https://jquery.com/) — included locally under the MIT license.

