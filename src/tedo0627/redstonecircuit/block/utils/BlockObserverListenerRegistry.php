<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\utils;

use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use tedo0627\redstonecircuit\RedstoneCircuit;

final class BlockObserverListenerRegistry{

    /** @var RegisteredListener[] $listeners */
    private static array $listeners = [];

    public static function register(Position $observerPosition, Position $monitoredPosition) : void{
        $hash = World::blockHash((int) $observerPosition->x, (int) $observerPosition->y, (int) $observerPosition->z) . $observerPosition->getWorld()->getFolderName();
        if(isset(self::$listeners[$hash])) {
            self::unregister($observerPosition);
        }
        self::$listeners[$hash] = Server::getInstance()->getPluginManager()->registerEvent(
            BlockUpdateEvent::class,
            static function(BlockUpdateEvent $event) use($observerPosition, $monitoredPosition) : void{
                if($event->getBlock()->getPosition()->equals($monitoredPosition)){
                    $event->getBlock()->getPosition()->getWorld()->scheduleDelayedBlockUpdate($observerPosition, 0);
                }
            },
            EventPriority::MONITOR,
            RedstoneCircuit::getInstance(),
            false
        );
    }

    public static function unregister(Position $observerPosition) : void{
        $hash = World::blockHash((int) $observerPosition->x, (int) $observerPosition->y, (int) $observerPosition->z) . $observerPosition->getWorld()->getFolderName();
        $registeredListener = self::$listeners[$hash];
        unset(self::$listeners[$hash]);
        HandlerListManager::global()->getListFor(BlockUpdateEvent::class)->unregister($registeredListener);
    }

}
