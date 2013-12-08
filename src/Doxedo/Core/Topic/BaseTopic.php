<?php

namespace Doxedo\Core\Topic;

use Doxedo\Core\Scope;

class BaseTopic
{
    protected $id = "";
    protected $title = "";
    protected $src = "";
    protected $depth;
    protected $filename;

    protected $topics = array(); //TODO: see notes in project
    protected $scope;

    public function __construct()
    {
        $this->scope = new Scope();
    }

    public function getTopics()
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic)
    {
        return $this->topics[] = $topic;
    }

    // Depth within the current project
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    // Depth within the current project
    public function getDepth()
    {
        return $this->depth;
    }

    public function getFilename()
    {
        return $this->filename;
    }

}