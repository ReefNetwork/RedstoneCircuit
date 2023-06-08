<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\world\sound\BlockPlaceSound;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;

class ShulkerBoxDispenseBehavior implements DispenseItemBehavior{

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        $side = $block->getSide($block->getFacing());
        $pos = $side->getPosition();
        $world = $pos->getWorld();
        if($side->getTypeId() !== BlockTypeIds::AIR) {
            return false;
        }

        $finalFacing = $block->getFacing();
        foreach(Facing::ALL as $facing) {
            if($side->getSide($facing)->getTypeId() === BlockTypeIds::AIR) {
                $finalFacing = $facing;
                break;
            }
        }

        $world->setBlock($side->getPosition(), VanillaBlocks::SHULKER_BOX()->setFacing($finalFacing));
        $world->addSound($pos, new BlockPlaceSound(VanillaBlocks::SHULKER_BOX()));
        return true;
    }
}
