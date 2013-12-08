<?php

namespace Doxedo\Core;

use Doxedo\Core\Project;
use Doxedo\Core\Scope;
use InvalidArgumentException;
use RuntimeException;

/**
 * TODO: Extract all echo's and use a PSR3 logging interface
 */

class Build
{
    private $project;
    private $buildroot; // build root (i.e. build/)
    private $logger;

    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
        $this->logger->addInfo('Enabling logger');
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setBuildRoot($path)
    {
        $path = rtrim($path , '/');
        if (!file_exists($path)) {
            throw new InvalidArgumentException('Buildroot does not exist: ' . $path);
        }
        $this->buildroot = $path;
    }

    public function getBuildRoot()
    {
        return $this->buildroot;
    }

    public function build() {
        if (!$this->project) {
            throw new RuntimeException("Publishing project that has not yet been loaded.");
            return null();
        }

        if ($this->buildroot == '') {
            $this->buildroot = $this->project->getBasePath() . "/build";
        }

        if (!file_exists($this->buildroot)) {
            throw new RuntimeException('build.buildroot does not exist:' . $this->buildroot);
        }
        
        foreach($this->project->getLanguages() as $language) {
            foreach($language->getTargets() as $target) {
                $this->getLogger("Publishing target: " . $language->getName()  . '/' . $target->getName());
                $target->publish($this);
            }
        }

    }

}