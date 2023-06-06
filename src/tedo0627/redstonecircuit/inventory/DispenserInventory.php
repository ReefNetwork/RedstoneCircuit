<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\world\Position;
use function array_rand;
use function count;

class DispenserInventory extends SimpleInventory implements BlockInventory{
    use BlockInventoryTrait;

    public function __construct(Position $holder){
        $this->holder = $holder;
        parent::__construct(9);
    }

    public function getRandomSlot() : int{
        $slots = [];
        for($slot = 0; $slot < $this->getSize(); $slot++){
            if(!$this->getItem($slot)->isNull()) $slots[] = $slot;
        }

        return count($slots) === 0 ? -1 : $slots[array_rand($slots)];
    }
}
