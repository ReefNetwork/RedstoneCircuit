<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\Air;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\FlintSteel;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class FlintSteelDispenseBehavior implements DispenseItemBehavior{

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        if(!$dispensedItem instanceof FlintSteel) {
            return false;
        }

        $blockClicked = $block->getSide($block->getFacing());
        if($blockClicked instanceof Air){
            $blockClicked->getPosition()->getWorld()->setBlock($blockClicked->getPosition(), VanillaBlocks::FIRE());
            $dispensedItem->applyDamage(1);
            $remainingItem = $dispensedItem;
            return true;
        }elseif($blockClicked->onInteract($dispensedItem, Facing::opposite($block->getFacing()), Vector3::zero())) {
            $remainingItem = $dispensedItem;
            return true;
        }
        return false;
    }
}
