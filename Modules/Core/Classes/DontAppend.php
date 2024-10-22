<?php

namespace Modules\Core\Classes;

use Shetabit\Shopit\Modules\Core\Classes\DontAppend as BaseDontAppend;

class DontAppend
{
    public function __construct(protected mixed $name)
    {
//        echo $name;
    }
}
