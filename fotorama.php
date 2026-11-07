<?php
// Fotorama extension for Datenstrom Yellow
// https://github.com/kodersha/yellow-fotorama

class YellowFotorama {
    const VERSION = "1.3.0";

    public $yellow;
    private $assetsRequired = false;

    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        if ($name=="fotorama" && ($type=="block" || $type=="inline")) {
            return $this->getGalleryHtml($page, $text);
        }
        return null;
    }

    public function onParseContentShortcut($page, $name, $text, $type) {
        if ($name=="fotorama" && ($type=="block" || $type=="inline")) {
            return $this->getGalleryHtml($page, $text);
        }
        return null;
    }

    private function getGalleryHtml($page, $text) {
        $imageNames = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($imageNames)) {
            $page->error(400, "Fotorama requires one or more image filenames!");
            return null;
        }

        if (!$this->yellow->extension->isExisting("image")) {
            $page->error(500, "Fotorama requires the Yellow Image extension!");
            return null;
        }
        $imageExtension = $this->yellow->extension->get("image");
        if (!is_object($imageExtension)) {
            $page->error(500, "Fotorama could not load the Yellow Image extension!");
            return null;
        }

        // Build a filename index from Yellow's configured image directory.
        // Explicit names are then emitted in exactly the order used in the shortcut.
        $mediaFiles = $this->yellow->media->index()->match(
            "#^".preg_quote($this->yellow->system->get("coreImageLocation"), "#").".+#",
            true
        );
        $filesByName = array();
        foreach ($mediaFiles as $file) {
            $fileName = basename(str_replace("\\", "/", $file->location));
            $filesByName[$fileName] = $file;
        }

        $matchedFiles = array();
        $missingFiles = array();
        foreach ($imageNames as $imageName) {
            // Accept filenames only, not paths or traversal segments.
            if ($imageName!==basename(str_replace("\\", "/", $imageName)) ||
                $imageName=="." || $imageName==".." ||
                strpos($imageName, "/")!==false || strpos($imageName, "\\")!==false) {
                $missingFiles[] = $imageName;
                continue;
            }
            if (isset($filesByName[$imageName])) {
                $matchedFiles[] = $filesByName[$imageName];
            } else {
                $missingFiles[] = $imageName;
            }
        }
        if (!empty($missingFiles)) {
            $page->error(404, "Fotorama image files not found in ".$this->yellow->system->get("coreImageLocation").": ".implode(", ", $missingFiles));
            return null;
        }

        $output = '<div class="fotorama-gallery">';
        $output .= '<div class="fotorama" data-allowfullscreen="true" data-fit="cover">';
        $modified = 0;
        foreach ($matchedFiles as $file) {
            $extension = strtolower(pathinfo($file->fileName, PATHINFO_EXTENSION));
            if ($extension=="webp") {
                // Serve WebP directly because the site's WebP worker removes
                // the original JPEG/PNG after conversion.
                $src = $this->yellow->system->get("coreServerBase").$file->location;
                list($width, $height, $orientation, $type) =
                    $this->yellow->toolbox->detectImageInformation($file->fileName);
            } else {
                list($src, $width, $height) =
                    $imageExtension->getImageInformation($file->fileName, "100%", "100%");
            }

            $alt = $file->get("title");
            if (is_string_empty($alt)) $alt = basename($file->fileName);
            $output .= '<img src="'.htmlspecialchars($src, ENT_QUOTES, "UTF-8").'"';
            if (!empty($width) && !empty($height)) {
                $output .= ' width="'.htmlspecialchars($width, ENT_QUOTES, "UTF-8").'"';
                $output .= ' height="'.htmlspecialchars($height, ENT_QUOTES, "UTF-8").'"';
            }
            $output .= ' alt="'.htmlspecialchars($alt, ENT_QUOTES, "UTF-8").'" loading="lazy" />';
            $modified = max($modified, $this->yellow->toolbox->getFileModified($file->fileName));
        }
        $output .= '</div></div>';

        if ($modified) $page->setLastModified($modified);
        $this->assetsRequired = true;
        return $output;
    }

    public function onParsePageExtra($page, $name) {
        if ($name!="header") return null;
        if (preg_match('/\\[fotorama(?:\\s+[^\\]]*)?\\]/i', $page->getContentRaw())) {
            $this->assetsRequired = true;
        }
        if (!$this->assetsRequired) return null;

        $base = rtrim($this->yellow->system->get("coreServerBase"), "/");
        $directory = trim($this->yellow->system->get("coreExtensionLocation"), "/");
        $extensionLocation = htmlspecialchars($base."/".$directory."/", ENT_QUOTES, "UTF-8");

        $output = '<link rel="stylesheet" href="'.$extensionLocation.'fotorama.css" />'."\n";
        $output .= '<script defer src="'.$extensionLocation.'jquery.min.js"></script>'."\n";
        $output .= '<script defer src="'.$extensionLocation.'fotorama.js"></script>'."\n";
        return $output;
    }
}
