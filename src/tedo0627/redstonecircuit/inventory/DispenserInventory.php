<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\world\Position;

class DispenserInventory extends SimpleInventory implements BlockInventory{
    use BlockInventoryTrait;

    public function __construct(Position $holder){
        $this->holder = $holder;
        parent::__construct(9);
    }
}
