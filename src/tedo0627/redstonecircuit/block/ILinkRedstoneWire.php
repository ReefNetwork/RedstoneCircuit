<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block;

interface ILinkRedstoneWire{

    public function isConnect(int $face) : bool;
}
