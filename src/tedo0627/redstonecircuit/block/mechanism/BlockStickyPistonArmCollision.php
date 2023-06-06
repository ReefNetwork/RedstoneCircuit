<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

class BlockStickyPistonArmCollision extends BlockPistonArmCollision{

    public function isSticky() : bool{
        return true;
    }
}
