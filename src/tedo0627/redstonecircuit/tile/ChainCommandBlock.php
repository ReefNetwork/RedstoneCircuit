<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\tile;

use tedo0627\redstonecircuit\block\enums\CommandBlockType;

final class ChainCommandBlock extends CommandBlock{

    public function getCommandBlockType() : CommandBlockType{
        return CommandBlockType::CHAIN();
    }
}
