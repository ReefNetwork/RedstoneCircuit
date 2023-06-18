<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block;

trait LinkRedstoneWireTrait{

    public function isConnect(int $face) : bool{
        return true;
    }
}
