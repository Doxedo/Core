<?php

namespace Doxedo\Core\Target;

use Doxedo\Core\Build;
use Doxedo\Core\Target\TargetInterface;

class SinglePage extends AbstractTarget
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
    }
}