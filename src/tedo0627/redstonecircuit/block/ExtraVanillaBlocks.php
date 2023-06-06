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
use tedo0627\redstonecircuit\block\entity\BlockEntityCommand;
use tedo0627\redstonecircuit\block\entity\BlockEntityDispenser;
use tedo0627\redstonecircuit\block\entity\BlockEntityDropper;
use tedo0627\redstonecircuit\block\entity\BlockEntityMoving;
use tedo0627\redstonecircuit\block\entity\BlockEntityObserver;
use tedo0627\redstonecircuit\block\entity\BlockEntityPistonArm;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\block\mechanism\BlockDispenser;
use tedo0627\redstonecircuit\block\mechanism\BlockDropper;
use tedo0627\redstonecircuit\block\mechanism\BlockMoving;
use tedo0627\redstonecircuit\block\mechanism\BlockPiston;
use tedo0627\redstonecircuit\block\mechanism\BlockPistonArmCollision;
use tedo0627\redstonecircuit\block\mechanism\BlockStickyPiston;
use tedo0627\redstonecircuit\block\power\BlockObserver;
use tedo0627\redstonecircuit\block\power\BlockTarget;

/**
 * @generate-registry-docblock
 *
 * @method static BlockTarget TARGET()
 * @method static BlockObserver OBSERVER()
 * @method static BlockMoving MOVING_BLOCK()
 * @method static BlockPiston PISTON()
 * @method static BlockStickyPiston STICKY_PISTON()
 * @method static BlockPistonArmCollision PISTON_ARM_COLLISION()
 * @method static BlockPistonArmCollision STICKY_PISTON_ARM_COLLISION()
 * @method static BlockCommand COMMAND_BLOCK()
 * @method static BlockDispenser DISPENSER()
 * @method static BlockDropper DROPPER()
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
		self::register("command_block", new BlockCommand(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityCommand::class, CommandBlockType::NORMAL()), "Command Block", $indestructibleTypeInfo));
		$dispenserTypeInfo = new BlockTypeInfo(new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()));
		self::register("dispenser", new BlockDispenser(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityDispenser::class), "Dispenser", $dispenserTypeInfo));
		self::register("dropper", new BlockDropper(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityDropper::class), "Dropper", $dispenserTypeInfo));
		$pistonTypeInfo = new BlockTypeInfo(new BlockBreakInfo(1.5, BlockToolType::PICKAXE));
		self::register("piston", new BlockPiston(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityPistonArm::class), "Piston", $pistonTypeInfo));
		self::register("sticky_piston", new BlockStickyPiston(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityPistonArm::class), "Sticky Piston", $pistonTypeInfo));
		self::register("piston_arm", new BlockPistonArmCollision(new BlockIdentifier(BlockTypeIds::newId()), "Piston Arm Collision", $pistonTypeInfo));
		self::register("sticky_piston_arm", new BlockPistonArmCollision(new BlockIdentifier(BlockTypeIds::newId()), "Sticky Piston Arm Collision", $pistonTypeInfo));
		self::register("moving_block", new BlockMoving(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityMoving::class), "Moving Block", $indestructibleTypeInfo));
		$reusableTypeInfo = new BlockTypeInfo(new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel()));
		self::register("observer", new BlockObserver(new BlockIdentifier(BlockTypeIds::newId(), BlockEntityObserver::class), "Observer", $reusableTypeInfo));
		self::register("target", new BlockTarget(new BlockIdentifier(BlockTypeIds::newId()), "Target", $reusableTypeInfo));
	}
}
