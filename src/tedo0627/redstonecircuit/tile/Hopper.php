<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\tile;

use pocketmine\nbt\tag\CompoundTag;

class Hopper extends \pocketmine\block\tile\Hopper{

    protected int $transferCooldown = 0;
    protected int $tickedGameTime = 0;

    public function readSaveData(CompoundTag $nbt) : void{
        parent::readSaveData($nbt);
        $this->transferCooldown = $nbt->getInt("TransferCooldown", 0);
    }

    public function writeSaveData(CompoundTag $nbt) : void{
        parent::writeSaveData($nbt);
        $nbt->setInt("TransferCooldown", $this->transferCooldown);
    }

    public function getTransferCooldown() : int{
        return $this->transferCooldown;
    }

    public function setTransferCooldown(int $cooldown) : void{
        $this->transferCooldown = $cooldown;
    }

    public function getTickedGameTime() : int{
        return $this->tickedGameTime;
    }

    public function setTickedGameTime(int $tick) : void{
        $this->tickedGameTime = $tick;
    }
}
