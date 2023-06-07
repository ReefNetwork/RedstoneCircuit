<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block;

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
			new self("chain"),
            new self("impulse"),
            new self("repeating"),
        );
    }

    private function __construct(string $enumName){
        $this->Enum___construct($enumName);
    }
}
