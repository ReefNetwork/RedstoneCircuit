<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit;

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use tedo0627\redstonecircuit\block\ExtraVanillaBlocks;
use tedo0627\redstonecircuit\block\inventory\CommandInventory;
use tedo0627\redstonecircuit\block\inventory\DispenserInventory;
use tedo0627\redstonecircuit\block\inventory\DropperInventory;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\listener\CommandBlockListener;
use tedo0627\redstonecircuit\listener\TargetBlockListener;

class RedstoneCircuit extends PluginBase{

    private static bool $callEvent = false;

    /*public function onLoad(): void {
        // mechanism
        $this->addBlock("command_block", new BlockCommand(new BlockIdentifierFlattened(Ids::COMMAND_BLOCK, [Ids::REPEATING_COMMAND_BLOCK, Ids::CHAIN_COMMAND_BLOCK], 0, null, BlockEntityCommand::class), "Command Block", BlockBreakInfo::indestructible()));
        $this->addBlockEntity("command_block", BlockEntityCommand::class, ["CommandBlock", "minecraft:command_block"]);
        $info = new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        $this->addBlock("dispenser", new BlockDispenser(new BlockIdentifier(Ids::DISPENSER, 0, null, BlockEntityDispenser::class), "Dispenser", $info));
        $this->addBlockEntity("dispenser", BlockEntityDispenser::class, ["Dispenser", "minecraft:dispenser"]);
        $this->overrideBlock("door", Ids::IRON_DOOR_BLOCK, fn($bid, $name, $info) => new BlockIronDoor($bid, $name, $info));
        $this->overrideBlocks("door", [
            Ids::OAK_DOOR_BLOCK, Ids::SPRUCE_DOOR_BLOCK, Ids::BIRCH_DOOR_BLOCK,
            Ids::JUNGLE_DOOR_BLOCK, Ids::ACACIA_DOOR_BLOCK, Ids::DARK_OAK_DOOR_BLOCK
        ], fn($bid, $name, $info) => new BlockWoodenDoor($bid, $name, $info));
        $info = new BlockBreakInfo(3, BlockToolType::AXE, 0, 15);
        $this->addBlock("door", new BlockWoodenDoor(new BlockIdentifier(499, 0, 755), "Crimson Door", $info));
        $this->addItemBlock("door", 499, new ItemIdentifier(755, 0));
        $this->addBlock("door", new BlockWoodenDoor(new BlockIdentifier(500, 0, 756), "Warped Door", $info));
        $this->addItemBlock("door", 500, new ItemIdentifier(756, 0));
        $this->addBlock("dropper", new BlockDropper(new BlockIdentifier(Ids::DROPPER, 0, null, BlockEntityDropper::class), "Dropper", $info));
        $this->addBlockEntity("dropper", BlockEntityDropper::class, ["Dropper", "minecraft:dropper"]);
        $this->overrideBlocks("fence_gate", [
            Ids::OAK_FENCE_GATE, Ids::SPRUCE_FENCE_GATE, Ids::BIRCH_FENCE_GATE,
            Ids::JUNGLE_FENCE_GATE, Ids::DARK_OAK_FENCE_GATE, Ids::ACACIA_FENCE_GATE
        ], fn($bid, $name, $info) => new BlockFenceGate($bid, $name, $info));
        $info = new BlockBreakInfo(2, BlockToolType::AXE, 0, 15);
        $this->addBlock("fence_gate", new BlockFenceGate(new BlockIdentifier(513, 0), "Crimson Fence Gate", $info), true);
        $this->addBlock("fence_gate", new BlockFenceGate(new BlockIdentifier(514, 0), "Warped Fence Gate", $info), true);
        $this->overrideBlock("hopper", Ids::HOPPER_BLOCK, fn($bid, $name, $info) => new BlockHopper($bid, $name, $info), BlockEntityHopper::class);
        $this->addBlockEntity("hopper", BlockEntityHopper::class, ["Hopper", "minecraft:hopper"]);
        $this->overrideBlock("note_block", Ids::NOTEBLOCK, fn($bid, $name, $info) => new BlockNote($bid, $name, $info), BlockEntityNote::class);
        $this->addBlockEntity("note_block", BlockEntityNote::class, ["Music", "minecraft:noteblock"]);
        $info = new BlockBreakInfo(1.5, BlockToolType::PICKAXE);
        $this->addBlock("piston", new BlockPiston(new BlockIdentifier(Ids::PISTON, 0, null, BlockEntityPistonArm::class), "Piston", $info));
        $this->addBlock("piston", new BlockStickyPiston(new BlockIdentifier(Ids::STICKY_PISTON, 0, null, BlockEntityPistonArm::class), "Sticky Piston", $info));
        $this->addBlockEntity("piston", BlockEntityPistonArm::class, ["PistonArm", "minecraft:piston_arm"]);
        $this->addBlock("piston", new BlockPistonArmCollision(new BlockIdentifier(Ids::PISTONARMCOLLISION, 0, null), "Pistonarmcollision", $info));
        $this->addBlock("piston", new BlockStickyPistonArmCollision(new BlockIdentifier(472, 0, null), "Sticky Pistonarmcollision", $info));
        $this->addBlock("piston", new BlockMoving(new BlockIdentifier(Ids::MOVINGBLOCK, 0, null, BlockEntityMoving::class), "Moving Block", BlockBreakInfo::indestructible()));
        $this->addBlockEntity("piston", BlockEntityMoving::class, ["Movingblock", "minecraft:movingblock"]);
        $this->overrideBlock("rail", Ids::ACTIVATOR_RAIL, fn($bid, $name, $info) => new BlockActivatorRail($bid, $name, $info));
        $this->overrideBlock("rail", Ids::POWERED_RAIL, fn($bid, $name, $info) => new BlockPoweredRail($bid, $name, $info));
        $this->overrideBlock("redstone_lamp", Ids::REDSTONE_LAMP, fn($bid, $name, $info) => new BlockRedstoneLamp($bid, $name, $info));
        $this->overrideBlock("skull", Ids::SKULL_BLOCK, fn($bid, $name, $info) => new BlockSkull($bid, $name, $info), BlockEntitySkull::class);
        $this->addBlockEntity("skull", BlockEntitySkull::class, ["Skull", "minecraft:skull"]);
        $this->overrideBlock("tnt", Ids::TNT, fn($bid, $name, $info) => new BlockTNT($bid, $name, $info));
        $this->overrideBlock("trapdoor", Ids::IRON_TRAPDOOR, fn($bid, $name, $info) => new BlockIronTrapdoor($bid, $name, $info));
        $this->overrideBlocks("trapdoor", [
            Ids::WOODEN_TRAPDOOR, Ids::ACACIA_TRAPDOOR, Ids::BIRCH_TRAPDOOR,
            Ids::DARK_OAK_TRAPDOOR, Ids::JUNGLE_TRAPDOOR, Ids::SPRUCE_TRAPDOOR
        ], fn($bid, $name, $info) => new BlockWoodenTrapdoor($bid, $name, $info));
        $info = new BlockBreakInfo(3, BlockToolType::AXE, 0, 15);
        $this->addBlock("trapdoor", new BlockWoodenTrapdoor(new BlockIdentifier(501, 0), "Crimson Trapdoor", $info), true);
        $this->addBlock("trapdoor", new BlockWoodenTrapdoor(new BlockIdentifier(502, 0), "Warped Trapdoor", $info), true);

        // power
        $this->overrideBlock("button", Ids::STONE_BUTTON, fn($bid, $name, $info) => new BlockStoneButton($bid, $name, $info));
        $this->overrideBlocks("button", [
            Ids::WOODEN_BUTTON, Ids::ACACIA_BUTTON, Ids::BIRCH_BUTTON,
            Ids::DARK_OAK_BUTTON, Ids::JUNGLE_BUTTON, Ids::SPRUCE_BUTTON
        ], fn($bid, $name, $info) => new BlockWoodenButton($bid, $name, $info));
        $info = new BlockBreakInfo(0.5, BlockToolType::AXE);
        $this->addBlock("button", new BlockWoodenButton(new BlockIdentifier(515, 0), "Crimson Button", $info), true);
        $this->addBlock("button", new BlockWoodenButton(new BlockIdentifier(516, 0), "Warped Button", $info), true);
        $info = new BlockBreakInfo(0.5, BlockToolType::PICKAXE);
        $this->addBlock("button", new BlockStoneButton(new BlockIdentifier(551, 0), "Polished Blackstone Button", $info), true);
        $this->overrideBlock("daylight_sensor", Ids::DAYLIGHT_SENSOR, fn($bid, $name, $info) => new BlockDaylightSensor($bid, $name, $info));
        $this->overrideBlock("jukebox", Ids::JUKEBOX, fn($bid, $name, $info) => new BlockJukeBox($bid, $name, $info));
        $this->overrideBlock("lever", Ids::LEVER, fn($bid, $name, $info) => new BlockLever($bid, $name, $info));
        $info = new BlockBreakInfo(3.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        $this->addBlock("observer", new BlockObserver(new BlockIdentifier(Ids::OBSERVER, 0, null, BlockEntityObserver::class), "Observer", $info));
        $this->addBlockEntity("observer", BlockEntityObserver::class, ["Observer", "minecraft:observer"]);
        $this->overrideBlock("redstone_block", Ids::REDSTONE_BLOCK, fn($bid, $name, $info) => new BlockRedstone($bid, $name, $info));
        $this->overrideBlock("redstone_torch", Ids::REDSTONE_TORCH, fn($bid, $name, $info) => new BlockRedstoneTorch($bid, $name, $info));
        $this->overrideBlock("pressure_plate", Ids::STONE_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockStonePressurePlate($bid, $name, $info));
        $this->overrideBlocks("pressure_plate", [
            Ids::WOODEN_PRESSURE_PLATE, Ids::ACACIA_PRESSURE_PLATE, Ids::BIRCH_PRESSURE_PLATE,
            Ids::DARK_OAK_PRESSURE_PLATE, Ids::JUNGLE_PRESSURE_PLATE, Ids::SPRUCE_PRESSURE_PLATE
        ], fn($bid, $name, $info) => new BlockWoodenPressurePlate($bid, $name, $info));
        $this->addBlock("pressure_plate", new BlockWoodenPressurePlate(new BlockIdentifier(517, 0), "Crimson Pressure Plate", $info), true);
        $this->addBlock("pressure_plate", new BlockWoodenPressurePlate(new BlockIdentifier(518, 0), "Warped Pressure Plate", $info), true);
        $info = new BlockBreakInfo(0.5, BlockToolType::PICKAXE, ToolTier::WOOD()->getHarvestLevel());
        $this->addBlock("pressure_plate", new BlockStonePressurePlate(new BlockIdentifier(550, 0), "Polished Blackstone Pressure Plate", $info), true);
        $info = new BlockBreakInfo(0.5, BlockToolType::HOE);
        $this->addBlock("target", new BlockTarget(new BlockIdentifier(494, 0, null, BlockEntityTarget::class), "Target", $info));
        $this->addBlockEntity("target", BlockEntityTarget::class, ["Target", "minecraft:target"]);
        $this->overrideBlock("trapped_chest", Ids::TRAPPED_CHEST, fn($bid, $name, $info) => new BlockTrappedChest($bid, $name, $info), BlockEntityChest::class);
        $this->addBlockEntity("trapped_chest", BlockEntityChest::class, ["Chest", "minecraft:chest"]);
        $this->overrideBlock("tripwire", Ids::TRIPWIRE, fn($bid, $name, $info) => new BlockTripwire($bid, $name, $info));
        $this->overrideBlock("tripwire", Ids::TRIPWIRE_HOOK, fn($bid, $name, $info) => new BlockTripwireHook($bid, $name, $info));
        $this->addItemBlock("tripwire", Ids::TRIPWIRE, new ItemIdentifier(ItemIds::STRING, 0));
        $this->overrideBlock("weighted_pressure_plate", Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateHeavy($bid, $name, $info));
        $this->overrideBlock("weighted_pressure_plate", Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn($bid, $name, $info) => new BlockWeightedPressurePlateLight($bid, $name, $info));

        // transmission
        $this->overrideBlock("comparator", Ids::UNPOWERED_COMPARATOR, fn($bid, $name, $info) => new BlockRedstoneComparator($bid, $name, $info));
        $this->overrideBlock("redstone_wire", Ids::REDSTONE_WIRE, fn($bid, $name, $info) => new BlockRedstoneWire($bid, $name, $info));
        $this->addItemBlock("redstone_wire", Ids::REDSTONE_WIRE, new ItemIdentifier(ItemIds::REDSTONE, 0));
        $this->overrideBlock("repeater", Ids::UNPOWERED_REPEATER, fn($bid, $name, $info) => new BlockRedstoneRepeater($bid, $name, $info));

        $this->load();

        self::registerMappings();
        $this->getServer()->getAsyncPool()->addWorkerStartHook(function (int $worker): void {
            $this->getServer()->getAsyncPool()->submitTaskToWorker(new class extends AsyncTask {
                public function onRun(): void {
                    RedstoneCircuit::registerMappings();
                }
            }, $worker);
        });

        CreativeInventory::reset();
    }*/

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents(new CommandBlockListener(), $this);
        $this->getServer()->getPluginManager()->registerEvent(PlayerJoinEvent::class,
            function(PlayerJoinEvent $event){
                $callbackSet = $event->getPlayer()->getNetworkSession()->getInvManager()->getContainerOpenCallbacks(); // inventory manager always exists at this point
                $callbackSet->add(static fn(int $id, Inventory $inv) => $inv instanceof CommandInventory ? [ContainerOpenPacket::blockInv($id, WindowTypes::COMMAND_BLOCK, BlockPosition::fromVector3($inv->getHolder()))] : null);
                $callbackSet->add(static fn(int $id, Inventory $inv) => $inv instanceof DispenserInventory ? [ContainerOpenPacket::blockInv($id, WindowTypes::DISPENSER, BlockPosition::fromVector3($inv->getHolder()))] : null);
                $callbackSet->add(static fn(int $id, Inventory $inv) => $inv instanceof DropperInventory ? [ContainerOpenPacket::blockInv($id, WindowTypes::DROPPER, BlockPosition::fromVector3($inv->getHolder()))] : null);
            },
            EventPriority::LOWEST,
            $this,
            true
        );
        $this->getServer()->getPluginManager()->registerEvents(new TargetBlockListener(), $this);

        self::$callEvent = (bool) $this->getConfig()->get("event", false);

        self::registerBlocks();

        $this->getServer()->getAsyncPool()->addWorkerStartHook(function(int $worker) : void{
            $this->getServer()->getAsyncPool()->submitTaskToWorker(new class extends AsyncTask{
                public function onRun() : void{
                    RedstoneCircuit::registerBlocks();
                }
            }, $worker);
        });
    }

    public static function registerBlocks() : void{
        RuntimeBlockStateRegistry::getInstance()->register(ExtraVanillaBlocks::COMMAND_BLOCK());
        GlobalBlockStateHandlers::getDeserializer()->map(Ids::COMMAND_BLOCK, function(Reader $in) : Block{
            return ExtraVanillaBlocks::COMMAND_BLOCK()
                ->setConditionalMode($in->readBool(StateNames::CONDITIONAL_BIT))
                ->setFacing($in->readFacingDirection());
        });
        GlobalBlockStateHandlers::getSerializer()->map(ExtraVanillaBlocks::COMMAND_BLOCK(), function(BlockCommand $block) : Writer{
            return Writer::create(Ids::COMMAND_BLOCK)
                ->writeBool(StateNames::CONDITIONAL_BIT, $block->isConditionalMode())
                ->writeFacingDirection($block->getFacing());
        });
        StringToItemParser::getInstance()->registerBlock("command_block", fn() => clone ExtraVanillaBlocks::COMMAND_BLOCK());

        RuntimeBlockStateRegistry::getInstance()->register(ExtraVanillaBlocks::REPEATING_COMMAND_BLOCK());
        GlobalBlockStateHandlers::getDeserializer()->map(Ids::REPEATING_COMMAND_BLOCK, function(Reader $in) : Block{
            return ExtraVanillaBlocks::REPEATING_COMMAND_BLOCK()
                ->setConditionalMode($in->readBool(StateNames::CONDITIONAL_BIT))
                ->setFacing($in->readFacingDirection());
        });
        GlobalBlockStateHandlers::getSerializer()->map(ExtraVanillaBlocks::REPEATING_COMMAND_BLOCK(), function(BlockCommand $block) : Writer{
            return Writer::create(Ids::REPEATING_COMMAND_BLOCK)
                ->writeBool(StateNames::CONDITIONAL_BIT, $block->isConditionalMode())
                ->writeFacingDirection($block->getFacing());
        });
        StringToItemParser::getInstance()->registerBlock("repeating_command_block", fn() => clone ExtraVanillaBlocks::REPEATING_COMMAND_BLOCK());

        RuntimeBlockStateRegistry::getInstance()->register(ExtraVanillaBlocks::CHAIN_COMMAND_BLOCK());
        GlobalBlockStateHandlers::getDeserializer()->map(Ids::CHAIN_COMMAND_BLOCK, function(Reader $in) : Block{
            return ExtraVanillaBlocks::CHAIN_COMMAND_BLOCK()
                ->setConditionalMode($in->readBool(StateNames::CONDITIONAL_BIT))
                ->setFacing($in->readFacingDirection());
        });
        GlobalBlockStateHandlers::getSerializer()->map(ExtraVanillaBlocks::CHAIN_COMMAND_BLOCK(), function(BlockCommand $block) : Writer{
            return Writer::create(Ids::CHAIN_COMMAND_BLOCK)
                ->writeBool(StateNames::CONDITIONAL_BIT, $block->isConditionalMode())
                ->writeFacingDirection($block->getFacing());
        });
        StringToItemParser::getInstance()->registerBlock("chain_command_block", fn() => clone ExtraVanillaBlocks::CHAIN_COMMAND_BLOCK());

        self::registerSimpleBlock(Ids::DISPENSER, ExtraVanillaBlocks::DISPENSER(), ["Dispenser"]);
        self::registerSimpleBlock(Ids::DROPPER, ExtraVanillaBlocks::DROPPER(), ["dropper"]);
        self::registerSimpleBlock(Ids::PISTON, ExtraVanillaBlocks::PISTON(), ["piston"]);
        self::registerSimpleBlock(Ids::STICKY_PISTON, ExtraVanillaBlocks::STICKY_PISTON(), ["sticky_piston"]);
        self::registerSimpleBlock(Ids::OBSERVER, ExtraVanillaBlocks::OBSERVER(), ["observer"]);
        self::registerSimpleBlock(Ids::TARGET, ExtraVanillaBlocks::TARGET(), ["target"]);
    }

    /**
     * @param string[] $stringToItemParserNames
     */
    private static function registerSimpleBlock(string $id, Block $block, array $stringToItemParserNames) : void{
        RuntimeBlockStateRegistry::getInstance()->register($block);

        GlobalBlockStateHandlers::getDeserializer()->mapSimple($id, fn() => clone $block);
        GlobalBlockStateHandlers::getSerializer()->mapSimple($block, $id);

        foreach($stringToItemParserNames as $name){
            StringToItemParser::getInstance()->registerBlock($name, fn() => clone $block);
        }
    }

    public static function isCallEvent() : bool{
        return self::$callEvent;
    }
}
