<?php

namespace Doxedo\Core\Target;

use Doxedo\Core\Build;
use Doxedo\Core\Target\TargetInterface;
use RuntimeException;

class Pdf extends AbstractTarget
{
    public function publish(Build $build)
    {

        $publishpath = $build->getBuildRoot() . '/' . (string)$this->getLanguage()->getName() . '/' . $this->getName();
        if (!file_exists($publishpath)) {
            mkdir($publishpath, 0777, true);
        }

        $html = $this->getHtml();

        $html = $this->wrapTemplate($html);

        $build->getLogger()->addWarning('Rendering into ' . $publishpath);
        file_put_contents($publishpath . "/index.html", $html);

        $wkhtmltopdfpath = $this->getWkHtmlToPdfPath();
        $cmd = $wkhtmltopdfpath . ' ' . $publishpath . "/index.html" . ' ' . $publishpath . "/index.pdf";
        exec($cmd);
    }

    private function getWkHtmlToPdfPath()
    {
        switch (php_uname('s')) {
            case 'Darwin':
                // Install the i386 dmg! even on 64bit MacBook Pro
                return '/Applications/wkhtmltopdf.app/Contents/MacOS/wkhtmltopdf';
        }

        switch (php_uname("m")) {
            case "i386":
            case "i486":
            case "i586":
            case "i686":
                return 'wkhtmltopdf-i386';

            case "x86_64":
            case "amd64":
                return 'bin/wkhtmltopdf-amd64';
                
            default:
                throw new RuntimeException("Unsupported machine-type for wkhtmltopdf binary: " . php_uname("m"));
        }
    }
}