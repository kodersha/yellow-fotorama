# Fotorama for Datenstrom Yellow

Responsive image galleries for Datenstrom Yellow 1.x, powered by [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama).

## Requirements

- Datenstrom Yellow 1.x.
- The Yellow Image extension must be installed and enabled.
- PHP GD (or the image backend required by the installed Yellow Image extension).
- PHP ZipArchive for installation from a ZIP package through Yellow's pending installer.

jQuery 3.7.1 is bundled locally. The extension does not depend on a CDN or a theme-provided jQuery copy.

## Install with Yellow's ZIP installer

1. Download the ZIP archive: [yellow-fotorama-main.zip](https://github.com/kodersha/yellow-fotorama/archive/refs/heads/main.zip).
2. Do **not** extract it.
3. Copy the ZIP file into your website's `system/extensions/` directory and rename it to `fotorama.zip`.
4. Open any page of your Yellow website. Yellow detects the pending ZIP package and installs the files declared in `extension.ini`. The ZIP is removed after processing.
5. Confirm that these files now exist in `system/extensions/`:
   - `fotorama.php`
   - `fotorama.js`
   - `fotorama.css`
   - `jquery.min.js`
   - `jquery.LICENSE.txt`
   - `fotorama.png`
   - `fotorama@2x.png`

The PHP extension and its browser assets belong in `system/extensions/`, **not** in `system/workers/`, `system/themes/` or `media/`. The `extension.ini` file is the installation manifest inside the ZIP; Yellow reads it and places each declared file in its destination.

If the ZIP remains in `system/extensions/` and nothing happens, check that PHP's ZipArchive extension is enabled, that PHP can write to `system/extensions/`, and inspect `system/extensions/yellow-website.log`.

## Manual installation

If Yellow's ZIP installer is unavailable:

1. Extract the archive outside your website.
2. Copy the seven runtime files listed above into `system/extensions/`.
3. Ensure the Yellow Image extension is installed and enabled.
4. Reload the page containing the gallery.

Do not copy the repository folder itself into `system/extensions/`; copy the individual files so that `fotorama.php` is directly inside that directory.

## Usage

Add a shortcut to a Yellow page:

```text
[fotorama image-.*.jpg]
```

The first argument is a regular expression matched against files in Yellow's configured image directory. The optional second argument sets the thumbnail width and height; the default is `100%`.

Examples:

```text
[fotorama image-.*]
[fotorama vacation-.*\\.jpg 800]
```

The extension uses Yellow's media index and the Yellow Image extension to generate image URLs and thumbnails. It supports custom image-directory locations. Fotorama's CSS, jQuery and JavaScript are loaded only on pages containing a Fotorama shortcut.

## Update

Install the newer ZIP package using the same Yellow ZIP installer procedure. Yellow uses the manifest to update the extension files. Existing page content and shortcuts are preserved; no database migration or configuration entry is required.

## Uninstall

Use Yellow's Update extension uninstall command if the extension was installed from the ZIP package. For a manual installation, remove the seven runtime files listed above from `system/extensions/`.

## Included libraries

- [Fotorama 4.6.4](https://github.com/artpolikarpov/fotorama) — image gallery.
- [jQuery 3.7.1](https://jquery.com/) — bundled locally under its MIT license.
