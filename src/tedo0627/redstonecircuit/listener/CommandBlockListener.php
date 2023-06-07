<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\permission\DefaultPermissions;
use tedo0627\redstonecircuit\block\enums\CommandBlockType;
use tedo0627\redstonecircuit\tile\CommandBlock;

class CommandBlockListener implements Listener{

    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if(!$packet instanceof CommandBlockUpdatePacket) return;

        $player = $event->getOrigin()->getPlayer();
        if($player === null) return;
        if(!$packet->isBlock) return;

        if(!$player->isCreative() || !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;

        $pos = $packet->blockPosition;
        $world = $player->getWorld();
        $tile = $world->getTileAt($pos->getX(), $pos->getY(), $pos->getZ());
        if(!$tile instanceof CommandBlock) return;

        $tile->updateInformation(
            $packet->name,
            match($packet->commandBlockMode) {
                CommandBlockType::IMPULSE()->lpCommandMode => CommandBlockType::IMPULSE(),
                CommandBlockType::REPEATING()->lpCommandMode => CommandBlockType::REPEATING(),
                CommandBlockType::CHAIN()->lpCommandMode => CommandBlockType::CHAIN(),
            },
            $packet->isConditional,
            $packet->isRedstoneMode,
            $packet->command,
			$packet->shouldTrackOutput,
            $packet->lastOutput
        );
		$world->scheduleDelayedBlockUpdate($pos, $packet->executeOnFirstTick ? 1 : $packet->tickDelay);
    }
}
