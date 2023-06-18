<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\tile;

use pocketmine\nbt\tag\CompoundTag;

class MobHead extends \pocketmine\block\tile\MobHead{

    private bool $mouthMoving = false;

    public function readSaveData(CompoundTag $nbt) : void{
        parent::readSaveData($nbt);
        $this->mouthMoving = $nbt->getByte("MouthMoving", 0) !== 0;
    }

    public function writeSaveData(CompoundTag $nbt) : void{
        parent::writeSaveData($nbt);
        $nbt->setByte("MouthMoving", $this->mouthMoving ? 1 : 0);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        parent::addAdditionalSpawnData($nbt);
        $nbt->setByte("MouthMoving", $this->mouthMoving ? 1 : 0);
    }

    public function isMouthMoving() : bool{
        return $this->mouthMoving;
    }

    public function setMouthMoving(bool $mouthMoving) : void{
        $this->mouthMoving = $mouthMoving;
    }
}
