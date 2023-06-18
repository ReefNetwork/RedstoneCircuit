<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\mechanism;

use pocketmine\block\Transparent;
use tedo0627\redstonecircuit\block\MovingBlockTrait;
use tedo0627\redstonecircuit\tile\MovingBlock;
use function assert;

class BlockMoving extends Transparent{
    use MovingBlockTrait;

    public function readStateFromWorld() : \pocketmine\block\Block{
        parent::readStateFromWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        if(!$tile instanceof MovingBlock) return;

        $this->setExpanding($tile->isExpanding());

        $this->setPistonPosX($tile->getPistonPosX());
        $this->setPistonPosY($tile->getPistonPosY());
        $this->setPistonPosZ($tile->getPistonPosZ());

        $this->setMovingBlockName($tile->getMovingBlockName());
        $this->setMovingBlockStates($tile->getMovingBlockStates());
        $this->setMovingEntity($tile->getMovingEntity());
    }

    public function writeStateToWorld() : void{
        parent::writeStateToWorld();
        $tile = $this->getPosition()->getWorld()->getTile($this->getPosition());
        assert($tile instanceof MovingBlock);

        $tile->setExpanding($this->isExpanding());

        $tile->setPistonPosX($this->getPistonPosX());
        $tile->setPistonPosY($this->getPistonPosY());
        $tile->setPistonPosZ($this->getPistonPosZ());

        $tile->setMovingBlockName($this->getMovingBlockName());
        $tile->setMovingBlockStates($this->getMovingBlockStates());
        $tile->setMovingEntity($this->getMovingEntity());
    }
}
