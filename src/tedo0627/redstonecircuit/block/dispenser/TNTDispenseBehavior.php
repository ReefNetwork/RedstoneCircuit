<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\world\sound\BlockPlaceSound;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class TNTDispenseBehavior implements DispenseItemBehavior{

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        $pos = $block->getPosition()->getSide($block->getFacing());
        $world = $pos->getWorld();
        $world->setBlock($pos, VanillaBlocks::TNT());
        $world->addSound($pos, new BlockPlaceSound(VanillaBlocks::TNT()));
        /** @var TNT $tnt */
        $tnt = $block->getSide($block->getFacing());
        $tnt->ignite();
        return true;
    }
}
