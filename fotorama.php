<?php
// Fotorama extension for Datenstrom Yellow
// https://github.com/kodersha/yellow-fotorama

class YellowFotorama {
    const VERSION = "1.1.1";

    public $yellow;
    private $assetsRequired = false;

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Handle page content element
    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        if ($name=="fotorama" && ($type=="block" || $type=="inline")) {
            return $this->getGalleryHtml($page, $text);
        }
        return null;
    }

    // Compatibility with older Yellow versions using content shortcuts
    public function onParseContentShortcut($page, $name, $text, $type) {
        if ($name=="fotorama" && ($type=="block" || $type=="inline")) {
            return $this->getGalleryHtml($page, $text);
        }
        return null;
    }

    // Return gallery HTML
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

        // Yellow's media index stores filesystem paths in fileName.
        // findMediaDirectory() returns the corresponding absolute media directory.
        $imageLocation = $this->yellow->lookup->findMediaDirectory("coreImageLocation");
        if (is_string_empty($imageLocation)) {
            $page->error(500, "Fotorama could not resolve the image directory!");
            return null;
        }
        $regex = "#".preg_quote($imageLocation, "#").str_replace("#", "\\#", $pattern)."#";
        if (@preg_match($regex, "") === false) {
            $page->error(400, "Fotorama received an invalid image filename pattern!");
            return null;
        }

        // Match against fileName, not location: the pattern includes a filesystem path.
        $files = $this->yellow->media->index()->match($regex, false);
        if ($files->isEmpty()) {
            $page->error(404, "Fotorama image pattern '$pattern' does not match any files!");
            return null;
        }

        $output = '<div class="fotorama-gallery">';
        $output .= '<div class="fotorama" data-allowfullscreen="true" data-fit="cover">';
        foreach ($files as $file) {
            list($src, $width, $height) = $imageExtension->getImageInformation($file->fileName, $size, $size);
            $alt = $file->get("title");
            if (is_string_empty($alt)) $alt = basename($file->fileName);
            $output .= '<img src="'.htmlspecialchars($src, ENT_QUOTES, "UTF-8").'"';
            if ($width && $height) {
                $output .= ' width="'.htmlspecialchars($width, ENT_QUOTES, "UTF-8").'"';
                $output .= ' height="'.htmlspecialchars($height, ENT_QUOTES, "UTF-8").'"';
            }
            $output .= ' alt="'.htmlspecialchars($alt, ENT_QUOTES, "UTF-8").'" loading="lazy" />';
        }
        $output .= '</div></div>';

        $page->setLastModified($files->getModified());
        $this->assetsRequired = true;
        return $output;
    }

    // Add assets only to pages containing the Fotorama shortcut.
    public function onParsePageExtra($page, $name) {
        if ($name!="header") return null;

        // Header extras are parsed before the body, so inspect the raw page content.
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
