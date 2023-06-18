<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class ArmorDispenseBehavior implements DispenseItemBehavior{

    private int $slot;

    public function __construct(int $slot){
        $this->slot = $slot;
    }

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        $side = $block->getSide($block->getFacing());
        $pos = $side->getPosition();
        foreach($pos->getWorld()->getNearbyEntities(new AxisAlignedBB($pos->x, $pos->y, $pos->z, $pos->x + 1, $pos->y + 1, $pos->z + 1)) as $entity){
            if(!$entity instanceof Living) continue;

            $entity->getArmorInventory()->setItem($this->slot, $dispensedItem);
            return true;
        }
        return DispenserBehaviorRegistry::DEFAULT()->dispense($block, $dispensedItem, $remainingItem);
    }
}
