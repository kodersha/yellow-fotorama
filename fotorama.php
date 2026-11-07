<?php
// Fotorama extension for Datenstrom Yellow
// https://github.com/kodersha/yellow-fotorama

class YellowFotorama {
    const VERSION = "1.2.0";

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
        list($pattern, $size) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($pattern)) {
            $page->error(400, "Fotorama requires an image filename pattern!");
            return null;
        }
        if (is_string_empty($size)) $size = "100%";

        if (!$this->yellow->extension->isExisting("image")) {
            $page->error(500, "Fotorama requires the Yellow Image extension!");
            return null;
        }
        $imageExtension = $this->yellow->extension->get("image");
        if (!is_object($imageExtension)) {
            $page->error(500, "Fotorama could not load the Yellow Image extension!");
            return null;
        }

        // Match the filename, not the absolute filesystem path. Yellow media
        // entries store their public URL in location and their disk path in fileName.
        $regex = "#^".str_replace("#", "\\#", $pattern)."$#i";
        if (@preg_match($regex, "") === false) {
            $page->error(400, "Fotorama received an invalid image filename pattern!");
            return null;
        }

        $files = $this->yellow->media->index()->match(
            "#^".preg_quote($this->yellow->system->get("coreImageLocation"), "#").".+#",
            true
        );
        $matchedFiles = array();
        foreach ($files as $file) {
            $fileName = basename(str_replace("\\", "/", $file->location));
            if (preg_match($regex, $fileName)) {
                $matchedFiles[] = $file;
            }
        }
        if (empty($matchedFiles)) {
            $page->error(404, "Fotorama image filename pattern '$pattern' does not match files in ".$this->yellow->system->get("coreImageLocation")."!");
            return null;
        }

        $output = '<div class="fotorama-gallery">';
        $output .= '<div class="fotorama" data-allowfullscreen="true" data-fit="cover">';
        $modified = 0;
        foreach ($matchedFiles as $file) {
            $extension = strtolower(pathinfo($file->fileName, PATHINFO_EXTENSION));
            if ($extension=="webp") {
                // The site's WebP worker removes the original JPEG/PNG after
                // conversion. Serve the resulting WebP directly; Yellow Image
                // cannot resize WebP thumbnails on its own.
                $src = $this->yellow->system->get("coreServerBase").$file->location;
                list($width, $height, $orientation, $type) =
                    $this->yellow->toolbox->detectImageInformation($file->fileName);
            } else {
                list($src, $width, $height) =
                    $imageExtension->getImageInformation($file->fileName, $size, $size);
            }

            $alt = $file->get("title");
            if (is_string_empty($alt)) $alt = basename($file->fileName);
            $output .= '<img src="'.htmlspecialchars($src, ENT_QUOTES, "UTF-8").'"';
            if (!empty($width) && !empty($height)) {
                $output .= ' width="'.htmlspecialchars($width, ENT_QUOTES, "UTF-8").'"';
                $output .= ' height="'.htmlspecialchars($height, ENT_QUOTES, "UTF-8").'"';
            }
            $output .= ' alt="'.htmlspecialchars($alt, ENT_QUOTES, "UTF-8").'" loading="lazy" />';
            $modified = max($modified, $file->modified);
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
