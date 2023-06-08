<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Opaque;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\utils\AnyFacingOppositePlayerTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;
use tedo0627\redstonecircuit\tile\Dispenser;

class BlockDispenser extends Opaque implements IRedstoneComponent{
    use AnyFacingOppositePlayerTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->facing($this->facing);
        $w->bool($this->powered);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if($player instanceof Player){
            $dispenserTile = $this->position->getWorld()->getTile($this->position);
            if($dispenserTile instanceof Dispenser){
                $player->setCurrentWindow($dispenserTile->getInventory());
            }
        }

        return true;
    }

    public function onScheduledUpdate() : void{
        $world = $this->position->getWorld();
        $dispenserTile = $world->getTile($this->position);
        if($dispenserTile instanceof Dispenser){
            $dispenserTile->onUpdate();
        }
    }

    public function onRedstoneUpdate() : void{
        $powered = BlockPowerHelper::isPowered($this);
        if($powered === $this->isPowered()) return;

        if(RedstoneCircuit::isCallEvent()){
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, $this->isPowered());
            $event->call();
            $powered = $event->getNewPowered();
            if($powered === $this->isPowered()) return;
        }

        $this->setPowered($powered);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        if($powered) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 4);
    }
}
