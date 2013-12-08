<?php

namespace Doxedo\Core\Target;

use Doxedo\Core\Build;
use Doxedo\Core\Target;

interface TargetInterface
{
    public function publish(Build $build);
}