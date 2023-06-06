<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\utils;

use pocketmine\block\Block;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function fmod;

trait AnyFacingOppositePlayerTrait{
    use AnyFacingTrait;

    /**
     * @see Block::place()
     */
    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if($player !== null){
            $this->facing = Facing::opposite($this->getAnyFacing($player));
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    private function getAnyFacing(Player $player) : int{
        $verticalAngle = fmod($player->getLocation()->pitch, 360);
        if($verticalAngle < 0){
            $verticalAngle += 360.0;
        }

        if(45 <= $verticalAngle && $verticalAngle < 135){
            return Facing::UP;
        }
        if(225 <= $verticalAngle && $verticalAngle < 315){
            return Facing::DOWN;
        }

        $angle = fmod($player->getLocation()->yaw, 360);
        if($angle < 0){
            $angle += 360.0;
        }

        if((0 <= $angle && $angle < 45) || (315 <= $angle && $angle < 360)){
            return Facing::SOUTH;
        }
        if(45 <= $angle && $angle < 135){
            return Facing::WEST;
        }
        if(135 <= $angle && $angle < 225){
            return Facing::NORTH;
        }

        return Facing::EAST;
    }
}
