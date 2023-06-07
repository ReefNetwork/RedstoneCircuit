<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;
use pocketmine\world\Position;
use tedo0627\redstonecircuit\block\enums\CommandBlockType;

class CommandInventory extends BaseInventory implements BlockInventory{
    use BlockInventoryTrait;

    public function __construct(
        Position $holder,
        private readonly CommandBlockType $commandBlockType
    ){
        $this->holder = $holder;
        parent::__construct();
    }

    public function getCommandBlockType() : CommandBlockType{ return $this->commandBlockType; }

    protected function internalSetItem(int $index, Item $item) : void{
        throw new \BadMethodCallException("Cannot set items in CommandInventory");
    }

    protected function internalSetContents(array $items) : void{
        throw new \BadMethodCallException("Cannot set items in CommandInventory");
    }

    public function getSize() : int{
        return 0;
    }

    public function getItem(int $index) : Item{
        throw new \BadMethodCallException("Cannot get items in CommandInventory");
    }

    public function getContents(bool $includeEmpty = false) : array{
        return [];
    }
}
