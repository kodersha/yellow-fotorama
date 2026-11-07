# Fotorama for Datenstrom Yellow

A lightweight integration of [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama) for Datenstrom Yellow 1.x.

## Requirements

- Datenstrom Yellow 1.x.
- PHP with access to the site's media files.
- PHP ZipArchive only when installing through Yellow's ZIP installer.

The extension does not require Yellow Image. It reads the selected media files from Yellow's media index and serves the original files directly, including WebP.

jQuery 3.7.1 is bundled locally. No CDN is used.

## Install

1. Download the ZIP archive from the repository.
2. Copy it into `system/extensions/` as `fotorama.zip`.
3. Open a page on the Yellow site and allow the ZIP installer to process it.
4. Check that the following files exist in `system/extensions/`: `fotorama.php`, `fotorama.js`, `fotorama.css`, `jquery.min.js`, `jquery.LICENSE.txt`, `fotorama.png`, and `fotorama@2x.png`.

For manual installation, extract the archive and copy those seven files directly into `system/extensions/`.

## Usage

Use a space-separated list of exact filenames:

```text
[fotorama 546465.webp 465464564.webp 6575675.webp]
```

The gallery displays images in the exact order specified. Filenames are resolved in Yellow's configured image directory (normally `media/images/`). Do not include directory paths or regular expressions.

If a filename is missing, the extension reports the missing name rather than silently changing the gallery. Multiple Fotorama shortcuts can be used on one page.

## Assets

Fotorama CSS, jQuery and JavaScript are added to the page header only when the page contains a Fotorama shortcut. All assets are served locally from `system/extensions/`.

## Update

Install the latest ZIP package using the same procedure. The manifest updates the extension files without changing page content.

## Included libraries

- Fotorama 4.6.4 — image gallery.
- jQuery 3.7.1 — JavaScript dependency, MIT license.
