<?php

namespace Doxedo\Core;

class Scope
{
    private $variable = array();
    private $parent;

    public function setParent(Scope $parent)
    {
        $this->parent = $parent;
    }

    public function define($key, $value)
    {
        $this->variable[$key] = $value;
    }

    public function get($key)
    {
        if (isset($this->variable[$key])) {
            return $this->variable[$key];
        }
        if ($this->parent) {
            return $this->parent->get($key);
        }
        return null;
    }

    public function getAll()
    {
        $all = $this->variable;
        if ($this->parent) {
            $all=array_merge($this->parent->getAll(), $this->variable);
        }
        return $all;
    }
}