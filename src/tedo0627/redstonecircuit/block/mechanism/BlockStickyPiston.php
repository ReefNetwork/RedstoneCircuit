<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;

class BlockStickyPiston extends BlockPiston{

    public function isSticky() : bool{
        return true;
    }

    public function getNewPistonArm() : Block{
        return BlockFactory::getInstance()->get(472, $this->getFacing());
    }
}
