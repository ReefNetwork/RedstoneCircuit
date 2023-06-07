<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\CommandBlockUpdatePacket;
use pocketmine\permission\DefaultPermissions;
use tedo0627\redstonecircuit\block\enums\CommandBlockType;
use tedo0627\redstonecircuit\tile\CommandBlock;
use function assert;

class CommandBlockListener implements Listener{

    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
        $packet = $event->getPacket();
        if(!$packet instanceof CommandBlockUpdatePacket) return;

        $player = $event->getOrigin()->getPlayer();
        assert($player !== null);
        if(!$packet->isBlock) return;

        if(!$player->isCreative() || !$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return; // TODO: better permission check

        $pos = $packet->blockPosition;
        $world = $player->getWorld();
        $tile = $world->getTileAt($pos->getX(), $pos->getY(), $pos->getZ());
        if(!$tile instanceof CommandBlock) return;

        $tile->updateInformation(
            $packet->name,
            match($packet->commandBlockMode) {
                CommandBlockType::REPEATING()->lpCommandMode => CommandBlockType::REPEATING(),
                CommandBlockType::CHAIN()->lpCommandMode => CommandBlockType::CHAIN(),
                default => CommandBlockType::IMPULSE(),
            },
            $packet->isConditional,
            $packet->isRedstoneMode,
            $packet->command,
            $packet->shouldTrackOutput,
            $packet->lastOutput,
            $packet->executeOnFirstTick,
            $packet->tickDelay,
        );
        $world->scheduleDelayedBlockUpdate(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $packet->executeOnFirstTick ? 0 : max($packet->tickDelay, 1));
    }
}
