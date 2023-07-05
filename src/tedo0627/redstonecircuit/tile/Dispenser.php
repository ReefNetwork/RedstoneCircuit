<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\tile;

use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\dispenser\DispenserBehaviorRegistry;
use pocketmine\inventory\Inventory;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use tedo0627\redstonecircuit\event\BlockDispenseEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;
use tedo0627\redstonecircuit\sound\ClickFailSound;
use function mt_rand;
use function str_replace;

class Dispenser extends Spawnable implements Container, Nameable{
    use NameableTrait;
    use ContainerTrait;

    protected DispenserInventory $inventory;

    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);
        $this->inventory = new DispenserInventory($this->getPosition());
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->loadName($nbt);
        $this->loadItems($nbt);
    }

    public function onUpdate() : bool{
        // TODO: move this to Block
        if($this->closed){
            return false;
        }

        $this->timings->startTiming();

        $item = $this->inventory->getItem($slot = mt_rand(0, $this->inventory->getSize() - 1));
        if($item->isNull()){
            $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickFailSound(1.2));
            return false;
        }

        /* @var BlockDispenser $block */
        $block = $this->getBlock();

        if(RedstoneCircuit::isCallEvent()){
            $event = new BlockDispenseEvent($block, $item);
            $event->call();
            if($event->isCancelled()) return false;
        }

        if(DispenserBehaviorRegistry::getAll()[str_replace(" ", "_", $item->getVanillaName())]?->dispense($block, $item->pop(), $item)) {
            $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickSound());
            $this->inventory->setItem($slot, $item);
        }else{
            $this->getPosition()->getWorld()->addSound($this->getPosition(), new ClickFailSound(1.2));
        }

        $this->timings->stopTiming();

        return false;
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $this->saveName($nbt);
        $this->saveItems($nbt);
    }

    public function getInventory() : Inventory{
        return $this->inventory;
    }

    public function getRealInventory() : Inventory{
        return $this->inventory;
    }

    public function getDefaultName() : string{
        return "Dispenser";
    }
}
