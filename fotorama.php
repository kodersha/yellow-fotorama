<?php
// Fotorama extension for Datenstrom Yellow 1.x
// https://github.com/kodersha/yellow-fotorama

class YellowFotorama {
    const VERSION = "2.0.0";

    public $yellow;
    private $assetsRequired = false;

    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Yellow 1.x parses shortcuts through onParseContentElement().
    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        if ($name!="fotorama" || ($type!="inline" && $type!="block")) {
            return null;
        }
        $this->assetsRequired = true;
        return $this->getGalleryHtml($page, $text);
    }

    private function getGalleryHtml($page, $text) {
        $names = preg_split('/\\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($names)) {
            $page->error(400, "Fotorama: add one or more image filenames.");
            return "";
        }

        // Read the configured image directory, but compare against Yellow's
        // public media locations rather than filesystem paths or regexes.
        $imageDirectory = trim(str_replace("\\", "/", $this->yellow->system->get("coreImageLocation")), "/");
        $imagePrefix = "/".$imageDirectory."/";
        $filesByName = array();

        foreach ($this->yellow->media->index() as $mediaFile) {
            $location = "/".ltrim(str_replace("\\", "/", $mediaFile->location), "/");
            if (strpos($location, $imagePrefix)!==0) continue;
            $name = basename($location);
            $filesByName[$name] = $mediaFile;
        }

        $selected = array();
        $missing = array();
        foreach ($names as $name) {
            // Only accept simple filenames. Paths are intentionally not allowed.
            if ($name!==basename(str_replace("\\", "/", $name)) ||
                $name=="." || $name==".." ||
                strpos($name, "/")!==false || strpos($name, "\\")!==false) {
                $missing[] = $name;
                continue;
            }
            if (isset($filesByName[$name])) {
                $selected[] = $filesByName[$name];
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            $page->error(404, "Fotorama: image files not found in ".$imagePrefix.": ".implode(", ", $missing));
            return "";
        }

        $serverBase = rtrim($this->yellow->system->get("coreServerBase"), "/");
        $html = '<div class="fotorama-gallery"><div class="fotorama" data-allowfullscreen="true" data-fit="contain">';
        $lastModified = 0;

        foreach ($selected as $mediaFile) {
            $diskPath = $mediaFile->fileName;
            if (!is_file($diskPath)) continue;

            $publicLocation = "/".ltrim(str_replace("\\", "/", $mediaFile->location), "/");
            $src = $serverBase.$publicLocation;
            $imageInfo = @getimagesize($diskPath);
            $width = is_array($imageInfo) ? $imageInfo[0] : 0;
            $height = is_array($imageInfo) ? $imageInfo[1] : 0;

            $alt = $mediaFile->get("title");
            if (trim($alt)=="") $alt = basename($diskPath);

            $html .= '<img src="'.htmlspecialchars($src, ENT_QUOTES, "UTF-8").'"';
            if ($width>0 && $height>0) {
                $html .= ' width="'.$width.'" height="'.$height.'"';
            }
            $html .= ' alt="'.htmlspecialchars($alt, ENT_QUOTES, "UTF-8").'" />';
            $modified = $this->yellow->toolbox->getFileModified($diskPath);
            if ($modified>$lastModified) $lastModified = $modified;
        }

        $html .= '</div></div>';
        if ($lastModified>0) $page->setLastModified($lastModified);
        return $html;
    }

    // Add local Fotorama assets only to pages that contain the shortcut.
    public function onParsePageExtra($page, $name) {
        if ($name!="header") return null;

        if (!$this->assetsRequired && method_exists($page, "getContentRaw")) {
            $this->assetsRequired = preg_match('/\\[fotorama(?:\\s+[^\\]]*)?\\]/i', $page->getContentRaw())==1;
        }
        if (!$this->assetsRequired) return null;

        $base = rtrim($this->yellow->system->get("coreServerBase"), "/");
        $assetDirectory = trim($this->yellow->system->get("coreAssetLocation"), "/");
        $assetUrl = $base."/".$assetDirectory."/";

        $html = '<link rel="stylesheet" href="'.htmlspecialchars($assetUrl."fotorama.css", ENT_QUOTES, "UTF-8").'" />'."\n";
        $html .= '<script defer src="'.htmlspecialchars($assetUrl."jquery.min.js", ENT_QUOTES, "UTF-8").'"></script>'."\n";
        $html .= '<script defer src="'.htmlspecialchars($assetUrl."fotorama.js", ENT_QUOTES, "UTF-8").'"></script>'."\n";
        return $html;
    }
}
