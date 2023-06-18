<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockToolType;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\item\ToolTier;
use pocketmine\utils\CloningRegistryTrait;
use tedo0627\redstonecircuit\block\enums\CommandBlockType;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use tedo0627\redstonecircuit\block\mechanism\BlockDropper;
use tedo0627\redstonecircuit\block\mechanism\BlockMoving;
use tedo0627\redstonecircuit\block\mechanism\BlockPiston;
use tedo0627\redstonecircuit\block\mechanism\BlockPistonArmCollision;
use tedo0627\redstonecircuit\block\mechanism\BlockStickyPiston;
use tedo0627\redstonecircuit\block\power\BlockObserver;
use tedo0627\redstonecircuit\block\power\BlockTarget;
use tedo0627\redstonecircuit\tile\ChainCommandBlock;
use tedo0627\redstonecircuit\tile\Dispenser;
use tedo0627\redstonecircuit\tile\Dropper;
use tedo0627\redstonecircuit\tile\ImpulseCommandBlock;
use tedo0627\redstonecircuit\tile\MovingBlock;
use tedo0627\redstonecircuit\tile\Observer;
use tedo0627\redstonecircuit\tile\PistonArm;
use tedo0627\redstonecircuit\tile\RepeatingCommandBlock;

/**
 * @generate-registry-docblock
 *
 * @method static BlockCommand COMMAND_BLOCK()
 * @method static BlockCommand CHAIN_COMMAND_BLOCK()
 * @method static BlockDispenser DISPENSER()
 * @method static BlockDropper DROPPER()
 * @method static BlockMoving MOVING_BLOCK()
 * @method static BlockObserver OBSERVER()
 * @method static BlockPiston PISTON()
 * @method static BlockPistonArmCollision PISTON_ARM_COLLISION()
 * @method static BlockCommand REPEATING_COMMAND_BLOCK()
 * @method static BlockStickyPiston STICKY_PISTON()
 * @method static BlockPistonArmCollision STICKY_PISTON_ARM_COLLISION()
 * @method static BlockTarget TARGET()
 */
final class ExtraVanillaBlocks{
    use CloningRegistryTrait;

    private function __construct(){
        //NOOP
    }

    protected static function register(string $name, Block $block) : void{
        self::_registryRegister($name, $block);
    }

    /**
     * @return Block[]
     * @phpstan-return array<string, Block>
     */
    public static function getAll() : array{
        //phpstan doesn't support generic traits yet :(
        /** @var Block[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup() : void{
        $indestructibleTypeInfo = new BlockTypeInfo(BlockBreakInfo::indestructible());
        self::register("command_block", new BlockCommand(new BlockIdentifier(BlockTypeIds::newId(), ImpulseCommandBlock::class), "Command Block", $indestructibleTypeInfo, CommandBlockType::IMPULSE()));
        self::register("repeating_command_block", new BlockCommand(new BlockIdentifier(BlockTypeIds::newId(), RepeatingCommandBlock::class), "Repeating Command Block", $indestructibleTypeInfo, CommandBlockType::REPEATING()));
        self::register("chain_command_block", new BlockCommand(new BlockIdentifier(BlockTypeIds::newId(), ChainCommandBlock::class), "Chain Command Block", $indestructibleTypeInfo, CommandBlockType::CHAIN()));
        $dispenserTypeInfo = new BlockTypeInfo(new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()));
        self::register("dispenser", new BlockDispenser(new BlockIdentifier(BlockTypeIds::newId(), Dispenser::class), "Dispenser", $dispenserTypeInfo));
        self::register("dropper", new BlockDropper(new BlockIdentifier(BlockTypeIds::newId(), Dropper::class), "Dropper", $dispenserTypeInfo));
        $pistonTypeInfo = new BlockTypeInfo(new BlockBreakInfo(1.5, BlockToolType::PICKAXE));
        self::register("piston", new BlockPiston(new BlockIdentifier(BlockTypeIds::newId(), PistonArm::class), "Piston", $pistonTypeInfo));
        self::register("sticky_piston", new BlockStickyPiston(new BlockIdentifier(BlockTypeIds::newId(), PistonArm::class), "Sticky Piston", $pistonTypeInfo));
        self::register("piston_arm", new BlockPistonArmCollision(new BlockIdentifier(BlockTypeIds::newId()), "Piston Arm Collision", $pistonTypeInfo));
        self::register("sticky_piston_arm", new BlockPistonArmCollision(new BlockIdentifier(BlockTypeIds::newId()), "Sticky Piston Arm Collision", $pistonTypeInfo));
        self::register("moving_block", new BlockMoving(new BlockIdentifier(BlockTypeIds::newId(), MovingBlock::class), "Moving Block", $indestructibleTypeInfo));
        $reusableTypeInfo = new BlockTypeInfo(new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()));
        self::register("observer", new BlockObserver(new BlockIdentifier(BlockTypeIds::newId()), "Observer", $reusableTypeInfo));
        self::register("target", new BlockTarget(new BlockIdentifier(BlockTypeIds::newId()), "Target", $reusableTypeInfo));
    }
}
