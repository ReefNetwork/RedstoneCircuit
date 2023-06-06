<?php
declare(strict_types=1);

namespace tedo0627\redstonecircuit\block;

use pocketmine\utils\EnumTrait;

/**
 * @generate-registry-docblock
 */
final class CommandBlockType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("normal"),
			new self("repeating"),
			new self("chain"),
		);
	}

	private function __construct(string $enumName){
		$this->Enum___construct($enumName);
	}
}