<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Air;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class BoneMealDispenseBehavior implements DispenseItemBehavior{

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        $side = $block->getSide($block->getFacing());
        return !$side instanceof Air && $side->onInteract($dispensedItem, Facing::opposite($block->getFacing()), $side->getPosition());
    }
}
