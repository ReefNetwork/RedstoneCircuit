<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\power;

use pocketmine\block\Opaque;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockUpdateHelper;
use tedo0627\redstonecircuit\block\ILinkRedstoneWire;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\block\utils\AnyFacingOppositePlayerTrait;
use tedo0627\redstonecircuit\block\utils\BlockObserverListenerRegistry;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;

class BlockObserver extends Opaque implements IRedstoneComponent, ILinkRedstoneWire{
    use AnyFacingOppositePlayerTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->facing($this->facing);
        $w->bool($this->powered);
    }

    public function onPostPlace() : void{
        BlockObserverListenerRegistry::register($this->getPosition(), $this->getPosition()->getSide($this->getFacing()));
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 0);
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
        BlockObserverListenerRegistry::unregister($this->getPosition());
        parent::onBreak($item, $player);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));
        return true;
    }

    public function onScheduledUpdate() : void{
        $powered = !$this->isPowered();
        if(RedstoneCircuit::isCallEvent()){
            $event = new BlockRedstonePowerUpdateEvent($this, $powered, !$powered);
            $event->call();
            $powered = $event->getNewPowered();
        }
        $this->setPowered($powered);
        $this->getPosition()->getWorld()->setBlock($this->getPosition(), $this);
        BlockUpdateHelper::updateDiodeRedstone($this, Facing::opposite($this->getFacing()));

        if($this->isPowered()) $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 4);
    }

    public function getStrongPower(int $face) : int{
        return $this->isPowered() && $this->getFacing() === $face ? 15 : 0;
    }

    public function getWeakPower(int $face) : int{
        return $this->isPowered() && $this->getFacing() === $face ? 15 : 0;
    }

    public function isPowerSource() : bool{
        return $this->isPowered();
    }

    public function isConnect(int $face) : bool{
        return $this->getFacing() === $face;
    }
}
