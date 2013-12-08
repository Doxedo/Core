<?php

namespace Doxedo\Core;

use Doxedo\Core\Target\TargetInterface;

class Language
{
    private $name;
    private $scope;
    private $project;
    private $targets = array();

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->scope = new Scope();
        $this->scope->setParent($project->getScope());
    }

    public function getProject()
    {
        return $this->project;
    }
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function addTarget(TargetInterface $target)
    {
        $this->targets[] = $target;
    }

    public function getTargets()
    {
        return $this->targets;
    }

}