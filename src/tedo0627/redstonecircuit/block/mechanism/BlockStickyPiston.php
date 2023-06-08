<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use tedo0627\redstonecircuit\block\ExtraVanillaBlocks;

class BlockStickyPiston extends BlockPiston{

    public function isSticky() : bool{
        return true;
    }

    public function getNewPistonArm() : Block{
        return ExtraVanillaBlocks::STICKY_PISTON_ARM_COLLISION();
    }
}
