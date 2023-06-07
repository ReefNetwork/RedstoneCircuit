<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\enums\CommandBlockType;
use tedo0627\redstonecircuit\block\IRedstoneComponent;
use tedo0627\redstonecircuit\block\RedstoneComponentTrait;
use tedo0627\redstonecircuit\event\BlockRedstonePowerUpdateEvent;
use tedo0627\redstonecircuit\RedstoneCircuit;
use tedo0627\redstonecircuit\tile\CommandBlock;

class BlockCommand extends Opaque implements IRedstoneComponent{
    use AnyFacingTrait;
    use PoweredByRedstoneTrait;
    use RedstoneComponentTrait;

    protected bool $conditionalMode = false;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, protected CommandBlockType $commandBlockType){
        parent::__construct($idInfo, $name, $typeInfo);
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->bool($this->conditionalMode);
        $w->facing($this->facing);
    }

    public function getCommandBlockType() : CommandBlockType{
        return $this->commandBlockType;
    }

    public function setCommandBlockType(CommandBlockType $commandBlockType) : BlockCommand{
        $this->commandBlockType = $commandBlockType;
        $commandBlockTile = $this->position->getWorld()->getTile($this->position);
        if($commandBlockTile instanceof CommandBlock){
            $commandBlockTile->setCommandBlockType($commandBlockType);
        }
        return $this;
    }

    public function isConditionalMode() : bool{
        return $this->conditionalMode;
    }

    public function setConditionalMode(bool $conditionalMode) : BlockCommand{
        $this->conditionalMode = $conditionalMode;
        $commandBlockTile = $this->position->getWorld()->getTile($this->position);
        if($commandBlockTile instanceof CommandBlock){
            $commandBlockTile->setLPCondionalMode($conditionalMode);
        }
        return $this;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if($player instanceof Player && $player->isCreative(true) && $player->hasPermission(DefaultPermissions::ROOT_OPERATOR)){ // TODO: create permission to test against
            $commandBlockTile = $this->position->getWorld()->getTile($this->position);
            if($commandBlockTile instanceof CommandBlock){
                $player->setCurrentWindow($commandBlockTile->getInventory());
            }
        }

        return true;
    }

    public function onNearbyBlockChange() : void{
        $this->onScheduledUpdate();
    }

    public function onScheduledUpdate() : void{
        $world = $this->position->getWorld();
        $commandBlockTile = $world->getTile($this->position);
        if($commandBlockTile instanceof CommandBlock && $commandBlockTile->onUpdate()){
            $world->scheduleDelayedBlockUpdate($this->position, 1); //TODO: check this
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
        if($powered){
            $this->onScheduledUpdate();
        }
    }

    public function readStateFromWorld() : Block{
        parent::readStateFromWorld();
        $commandBlockTile = $this->position->getWorld()->getTile($this->position);
        if($commandBlockTile instanceof CommandBlock){
            $this->conditionalMode = $commandBlockTile->isLPCondionalMode();
        }

        return $this;
    }

    public function writeStateToWorld() : void{
        parent::writeStateToWorld();
        $commandBlockTile = $this->position->getWorld()->getTile($this->position);
        if($commandBlockTile instanceof CommandBlock){
            $commandBlockTile->setLPCondionalMode($this->conditionalMode);
        }
    }
}
