<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Water;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class GlassBottleDispenseBehavior implements DispenseItemBehavior{

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        $side = $block->getSide($block->getFacing());
        if(!$side instanceof Water) {
            return DispenserBehaviorRegistry::DEFAULT()->dispense($block, $dispensedItem, $remainingItem);
        }

        $remainingItem = VanillaItems::WATER_POTION();
        return true;
    }
}
