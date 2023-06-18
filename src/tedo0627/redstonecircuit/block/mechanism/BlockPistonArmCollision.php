<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Transparent;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\player\Player;

class BlockPistonArmCollision extends Transparent{
    use AnyFacingTrait;

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
        $block = $this->getSide($this->getPistonFace());
        if($block instanceof BlockPiston) $this->getPosition()->getWorld()->useBreakOn($block->getPosition());
        return parent::onBreak($item, $player);
    }

    public function onNearbyBlockChange() : void{
        $block = $this->getSide($this->getPistonFace());
        if($block instanceof BlockPiston) return;

        $this->getPosition()->getWorld()->useBreakOn($this->getPosition());
    }

    public function getDrops(Item $item) : array{
        return [];
    }

    public function getPistonFace() : int{
        $face = $this->getFacing();
        return Facing::axis($face) !== Axis::Y ? $face : Facing::opposite($face);
    }

    public function isSticky() : bool{
        return false;
    }
}
