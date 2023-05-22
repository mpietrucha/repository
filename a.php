<?php

require 'vendor/autoload.php';

use Mpietrucha\Repository\Repository;
use Mpietrucha\Repository\Concerns\Repositoryable;

class R extends Repository
{

}

class A {
    use Repositoryable;

    public function __construct()
    {
        $this->withRepository(new R);
    }
}
