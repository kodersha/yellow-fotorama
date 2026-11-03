<?php
// Fotorama extension for Datenstrom Yellow 1.0
// https://github.com/kodersha/yellow-fotorama

class YellowFotorama {
    const VERSION = "1.0.0";

    public $yellow;
    private $assetsRequired = false;

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Handle page content element
    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        $output = null;
        if ($name == "fotorama" && ($type == "block" || $type == "inline")) {
            $output = $this->getGalleryHtml($page, $text);
        }
        return $output;
    }

    // Keep compatibility with older Yellow versions
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name == "fotorama" && ($type == "block" || $type == "inline")) {
            $output = $this->getGalleryHtml($page, $text);
        }
        return $output;
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
            $page->error(500, "Fotorama requires the Image extension!");
            return null;
        }

        $imageLocation = $this->yellow->system->get("coreImageLocation");
        $pattern = str_replace("#", "\\#", $pattern);
        $regex = "#".preg_quote($imageLocation, "#").$pattern."#";
        if (@preg_match($regex, "") === false) {
            $page->error(400, "Fotorama received an invalid image filename pattern!");
            return null;
        }

        $files = $this->yellow->media->index()->match($regex);
        if ($files->isEmpty()) {
            $page->error(404, "Fotorama image pattern '$pattern' does not match any files!");
            return null;
        }

        $imageExtension = $this->yellow->extension->get("image");
        $output = '<div class="fotorama-gallery">';
        $output .= '<div class="fotorama" data-allowfullscreen="true" data-fit="cover">';

        foreach ($files as $file) {
            list($src, $width, $height) = $imageExtension->getImageInformation($file->fileName, $size, $size);
            $alt = $file->get("title");
            $output .= '<img src="'.htmlspecialchars($src, ENT_QUOTES, "UTF-8").'"';
            if ($width && $height) {
                $output .= ' width="'.htmlspecialchars($width, ENT_QUOTES, "UTF-8").'"';
                $output .= ' height="'.htmlspecialchars($height, ENT_QUOTES, "UTF-8").'"';
            }
            $output .= ' alt="'.htmlspecialchars($alt, ENT_QUOTES, "UTF-8").'"';
            $output .= ' loading="lazy" />';
        }

        $output .= '</div></div>';
        $page->setLastModified($files->getModified());
        $this->assetsRequired = true;
        return $output;
    }

    // Add assets only to pages that contain a gallery
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name == "header" && $this->assetsRequired) {
            $base = $this->yellow->system->get("coreServerBase");
            $directory = $this->yellow->system->get("coreExtensionLocation");
            $extensionLocation = rtrim($base, "/")."/".trim($directory, "/")."/";
            $extensionLocation = htmlspecialchars($extensionLocation, ENT_QUOTES, "UTF-8");

            $output = "<link rel=\"stylesheet\" href=\"{$extensionLocation}fotorama.css\" />\n";
            $output .= "<script defer src=\"{$extensionLocation}jquery.min.js\"></script>\n";
            $output .= "<script defer src=\"{$extensionLocation}fotorama.js\"></script>\n";
        }
        return $output;
    }
}
