<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\enums;

use pocketmine\utils\EnumTrait;

/**
 * @generate-registry-docblock
 *
 * @method static CommandBlockType CHAIN()
 * @method static CommandBlockType IMPULSE()
 * @method static CommandBlockType REPEATING()
 */
final class CommandBlockType{
    use EnumTrait {
        __construct as Enum___construct;
    }

    protected static function setup() : void{
        self::registerAll(
            new self("chain", 2),
            new self("impulse", 0),
            new self("repeating", 1),
        );
    }

    private function __construct(string $enumName, readonly public int $lpCommandMode){
        $this->Enum___construct($enumName);
    }
}
