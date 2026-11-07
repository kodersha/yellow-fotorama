<?php
// Lightweight smoke test for the Yellow API calls used by the extension.
function is_string_empty($value) {
    return !isset($value) || $value === "";
}

require dirname(__DIR__)."/fotorama.php";

class FotoramaTestFile {
    public $fileName;
    public $location;
    private $title;

    public function __construct($fileName, $location, $title) {
        $this->fileName = $fileName;
        $this->location = $location;
        $this->title = $title;
    }

    public function get($key) {
        return $key=="title" ? $this->title : "";
    }
}

class FotoramaTestFiles implements IteratorAggregate {
    private $files;

    public function __construct($files) {
        $this->files = $files;
    }

    public function match($regex, $filterByLocation = true) {
        $this->files = array_values(array_filter($this->files, function($file) use ($regex, $filterByLocation) {
            $value = $filterByLocation ? $file->location : $file->fileName;
            return preg_match($regex, $value)===1;
        }));
        return $this;
    }

    public function isEmpty() {
        return count($this->files)==0;
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->files);
    }

    public function getModified() {
        return 123;
    }
}

class FotoramaTestMedia {
    public function index() {
        return new FotoramaTestFiles(array(
            new FotoramaTestFile("media/images/one.jpg", "/media/images/one.jpg", "One & Two"),
            new FotoramaTestFile("media/images/two.png", "/media/images/two.png", "Two")
        ));
    }
}

class FotoramaTestImage {
    public function getImageInformation($fileName, $width, $height) {
        return array("/media/thumbnails/preview.jpg", 800, 600);
    }
}

class FotoramaTestToolbox {
    public function getTextArguments($text) {
        return array($text, "100%");
    }
}

class FotoramaTestExtension {
    public function isExisting($name) {
        return $name=="image";
    }

    public function get($name) {
        return new FotoramaTestImage();
    }
}

class FotoramaTestLookup {
    public function findMediaDirectory($key) {
        return "media/images/";
    }
}

class FotoramaTestSystem {
    public function get($key) {
        $settings = array(
            "coreServerBase" => "",
            "coreExtensionLocation" => "system/extensions/"
        );
        return isset($settings[$key]) ? $settings[$key] : "";
    }
}

class FotoramaTestPage {
    public $errorCode = 0;

    public function error($code, $message) {
        $this->errorCode = $code;
    }

    public function setLastModified($modified) {
    }

    public function getContentRaw() {
        return "[fotorama .*\\.jpg]";
    }
}

$yellow = (object)array(
    "toolbox" => new FotoramaTestToolbox(),
    "extension" => new FotoramaTestExtension(),
    "lookup" => new FotoramaTestLookup(),
    "media" => new FotoramaTestMedia(),
    "system" => new FotoramaTestSystem()
);
$page = new FotoramaTestPage();
$extension = new YellowFotorama();
$extension->onLoad($yellow);
$html = $extension->onParseContentElement($page, "fotorama", ".*\\.jpg", "", "block");

if (!is_string($html) || strpos($html, "One &amp; Two")===false || strpos($html, "Two</")!==false || $page->errorCode!=0) {
    fwrite(STDERR, "Fotorama media-index matching test failed.\n");
    exit(1);
}
$header = $extension->onParsePageExtra($page, "header");
if (strpos($header, "system/extensions/fotorama.js")===false || strpos($header, "jquery.min.js")===false) {
    fwrite(STDERR, "Fotorama asset-header test failed.\n");
    exit(1);
}
echo "Fotorama smoke tests passed.\n";
