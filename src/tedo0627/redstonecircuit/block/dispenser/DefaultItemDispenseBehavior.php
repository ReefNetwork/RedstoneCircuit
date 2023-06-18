<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use function mt_rand;

class DefaultItemDispenseBehavior implements DispenseItemBehavior{

    public function dispense(BlockDispenser $block, Item $dispensedItem, Item &$remainingItem) : bool{
        $pos = $block->getPosition();
        $face = $block->getFacing();
        $v = mt_rand(0, 100) / 1000 + 0.2;
        return $pos->getWorld()->dropItem(
            $pos->add(0.5, 0.5, 0.5)->addVector(Vector3::zero()->getSide($face)->multiply(0.6)),
            $dispensedItem,
            new Vector3(
                mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($face) === Axis::X ? 1.0 : 0.0) * $v * (Facing::isPositive($face) ? 1.0 : -1.0),
                mt_rand(-100, 100) / 100 * 0.0075 * 6 + 0.2,
                mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($face) === Axis::Z ? 1.0 : 0.0) * $v * (Facing::isPositive($face) ? 1.0 : -1.0),
            )
        ) instanceof ItemEntity;
    }
}
