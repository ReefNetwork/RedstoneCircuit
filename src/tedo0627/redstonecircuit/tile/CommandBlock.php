<?php

namespace tedo0627\redstonecircuit\tile;

use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\server\CommandEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\CommandBlockTrait;
use tedo0627\redstonecircuit\block\CommandBlockType;
use tedo0627\redstonecircuit\block\inventory\CommandInventory;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\RedstoneCircuit;

abstract class CommandBlock extends Spawnable implements Nameable{
    use NameableTrait;
    use CommandBlockTrait;
    use PoweredByRedstoneTrait;

    public CONST TAG_AUTO = "auto";
    public CONST TAG_COMMAND = "command";
    public CONST TAG_CONDITIONAL_MODE = "conditionalMode";
    public CONST TAG_CONDITION_MET = "conditionMet";
    public CONST TAG_EXECUTE_ON_FIRST_TICK = "executeOnFirstTick";
    public CONST TAG_LAST_EXECUTION = "lastExecution";
    public CONST TAG_LAST_OUTPUT = "lastOutput";
    public CONST TAG_LAST_OUTPUT_PARAMS = "lastOutputParams";
    public CONST TAG_POWERED = "powered";
    public CONST TAG_SUCCESS_COUNT = "SuccessCount";
    public CONST TAG_TICK_DELAY = "tickDelay";
    public CONST TAG_TRACK_OUTPUT = "trackOutput";
    public CONST TAG_VERSION = "Version";

    protected CommandInventory $inventory;
    protected bool $auto = false;
    protected bool $conditionMet = false;
    protected bool $conditionalMode = false;
    protected string $command = "";
    protected int $commandBlockMode = 0;
    protected bool $executeOnFirstTick = false;
    protected string $lastOutput = "";
    protected int $successCount = 0;
    protected int $tickDelay = 0;

    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->inventory = new CommandInventory($this->getPosition(), $this->getCommandBlockType());
        $this->inventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
            static function(Inventory $_) use ($world, $pos) : void{
                $world->scheduleDelayedBlockUpdate($pos, 1);
            })
        );
    }

    public function getDefaultName(): string {
        return "CommandBlock";
    }

    public function close() : void{
        if(!$this->closed){
            $this->inventory->removeAllViewers();

            parent::close();
        }
    }

    public function getInventory() : CommandInventory{
        return $this->inventory;
    }

    public function getRealInventory() : CommandInventory{
        return $this->getInventory();
    }

    abstract public function getCommandBlockType() : CommandBlockType;

    protected function delay(): void {
        $this->setTick($this->getTickDelay());
    }

    protected function execute(): void {
        $successful = false;
        if ($this->check()) $successful = $this->dispatch();
        $this->setSuccessCount($successful ? 1 : 0);
        $this->writeStateToWorld();

        $block = $this->getSide($this->getFacing());
        if (!$block instanceof BlockCommand) return;
        if ($block->getCommandBlockMode() !== BlockCommand::CHAIN) return;

        $pos = $this->getPosition();
        $index = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        $block->chain([$index]);
    }

    protected function check(): bool {
        if ($this->getCommand() === "") return false;

        if ($this->isConditionalMode()) {
            $block = $this->getSide(Facing::opposite($this->getFacing()));
            if (!$block instanceof BlockCommand) return false;
            if ($block->getSuccessCount() <= 0) return false;
        }

        if ($this->isAuto()) return true;
        return BlockPowerHelper::isPowered($this);
    }

    protected function chain(array $blockIndex = []): void {
        $pos = $this->getPosition();
        $index = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        if (in_array($index, $blockIndex, true)) return;

        if ($this->getTickDelay() !== 0) {
            $this->delay();
            return;
        }

        $successful = false;
        if ($this->check()) $successful = $this->dispatch();
        $this->setSuccessCount($successful ? 1 : 0);
        $block = $this->getSide($this->getFacing());
        if (!$block instanceof BlockCommand) return;
        if ($block->getCommandBlockMode() !== BlockCommand::CHAIN) return;

        $pos = $this->getPosition();
        $blockIndex[] = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        $block->chain($blockIndex);
    }

    protected function dispatch(): bool {
        $command = $this->getCommand();
        if (RedstoneCircuit::isCallEvent()) {
            $event = new CommandEvent($this, $command);
            $event->call();
            if ($event->isCancelled()) return false;

            $command = $event->getCommand();
        }

        $args = [];
        preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $command, $matches);
        foreach($matches[0] as $k => $_){
            for($i = 1; $i <= 2; ++$i){
                if($matches[$i][$k] !== ""){
                    $args[$k] = $i === 1 ? stripslashes($matches[$i][$k]) : $matches[$i][$k];
                    break;
                }
            }
        }

        $successful = false;
        $sentCommandLabel = array_shift($args);
        if ($sentCommandLabel !== null && ($target = Server::getInstance()->getCommandMap()->getCommand($sentCommandLabel)) !== null) {
            $target->timings->startTiming();

            try {
                $result = $target->execute($this, $sentCommandLabel, $args);
                if (is_bool($result)) $successful = $result;
            } catch (InvalidCommandSyntaxException) {
                $this->sendMessage($this->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage())));
            } finally {
                $target->timings->stopTiming();
            }
        } else {
            $this->sendMessage(KnownTranslationFactory::pocketmine_command_notFound($sentCommandLabel ?? "", "/help")->prefix(TextFormat::RED));
        }
        return $successful;
    }

    public function onUpdate() : bool{
        //TODO: move this to Block
        if($this->closed){
            return false;
        }

        $this->timings->startTiming();

        $tick = $this->getTick();
        $mode = $this->getCommandBlockMode();
        if ($tick > 0) {
            if ($mode === BlockCommand::REPEATING && !$this->check()) {
                $this->setTick(-1);
                $this->writeStateToWorld();
                $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
                return;
            }

            $this->setTick($tick - 1);
            if ($tick === 1) {
                $this->execute();
                if ($mode === BlockCommand::REPEATING) $this->delay();
                return;
            }

            $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1);
            return;
        }

        if ($mode !== BlockCommand::REPEATING) return;

        if ($this->getTickDelay() === 0 || ($tick === -1 && $this->isExecuteOnFirstTick())) {
            $this->setTick(0);
            $this->execute();
        }
        $this->delay();

        $this->timings->stopTiming();

        return $ret;
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->powered = $nbt->getByte(self::TAG_POWERED, 0) === 1;
        $this->auto = $nbt->getByte(self::TAG_AUTO, 0) === 1;
        $this->conditionMet = $nbt->getByte(self::TAG_CONDITION_MET, 0) === 1;
        $this->conditionalMode = $nbt->getByte(self::TAG_CONDITIONAL_MODE, 0) === 1;
        $this->command = $nbt->getString(self::TAG_COMMAND, "");
        $this->version = $nbt->getInt(self::TAG_VERSION, 0);
        $this->successCount = $nbt->getInt(self::TAG_SUCCESS_COUNT, 0);
        $this->loadName($nbt);
        $this->lastOutput = $nbt->getString(self::TAG_LAST_OUTPUT, "");
        $this->lastOutputParams = $nbt->getListTag(self::TAG_LAST_OUTPUT_PARAMS)?->getAllValues() ?? [];
        $this->trackOutput = $nbt->getByte(self::TAG_TRACK_OUTPUT, 0) === 1;
        $this->lastExecution = $nbt->getLong(self::TAG_LAST_EXECUTION, 0);
        $this->tickDelay = $nbt->getInt(self::TAG_TICK_DELAY, 0);
        $this->executeOnFirstTick = $nbt->getByte(self::TAG_EXECUTE_ON_FIRST_TICK, 0) === 1;
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $nbt->setByte(self::TAG_POWERED, $this->powered ? 1 : 0);
        $nbt->setByte(self::TAG_AUTO, $this->auto ? 1 : 0);
        $nbt->setByte(self::TAG_CONDITION_MET, $this->conditionMet ? 1 : 0);
        $nbt->setByte(self::TAG_CONDITIONAL_MODE, $this->conditionalMode ? 1 : 0);
        $nbt->setString(self::TAG_COMMAND, $this->command);
        $nbt->setInt(self::TAG_VERSION, $this->version);
        $nbt->setInt(self::TAG_SUCCESS_COUNT, $this->successCount);
        $this->saveName($nbt);
        $nbt->setString(self::TAG_LAST_OUTPUT, $this->lastOutput);
        $nbt->setTag(self::TAG_LAST_OUTPUT_PARAMS, new ListTag(array_map(static fn(string $_) => new StringTag($_), $this->lastOutputParams), NBT::TAG_String));
        $nbt->setByte(self::TAG_TRACK_OUTPUT, $this->trackOutput ? 1 : 0);
        $nbt->setLong(self::TAG_LAST_EXECUTION, $this->lastExecution);
        $nbt->setInt(self::TAG_TICK_DELAY, $this->tickDelay);
        $nbt->setByte(self::TAG_EXECUTE_ON_FIRST_TICK, $this->executeOnFirstTick ? 1 : 0);
    }

    public function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->setString(self::TAG_COMMAND, $this->command);
        $nbt->setInt(self::TAG_TICK_DELAY, $this->tickDelay);
        $nbt->setByte(self::TAG_EXECUTE_ON_FIRST_TICK, $this->executeOnFirstTick ? 1 : 0);
        $nbt->setByte(self::TAG_TRACK_OUTPUT, $this->trackOutput ? 1 : 0);
        $nbt->setByte(self::TAG_CONDITIONAL_MODE, $this->conditionalMode ? 1 : 0);
        $nbt->setByte(self::TAG_AUTO, $this->auto ? 1 : 0);
        $nbt->setByte(self::TAG_POWERED, $this->powered ? 1 : 0);
        $nbt->setByte(self::TAG_CONDITION_MET, $this->conditionMet ? 1 : 0);
        $nbt->setInt(self::TAG_SUCCESS_COUNT, $this->successCount);
        $nbt->setString(self::TAG_LAST_OUTPUT, $this->lastOutput);
        $nbt->setTag(self::TAG_LAST_OUTPUT_PARAMS, new ListTag(array_map(static fn(string $_) => new StringTag($_), $this->lastOutputParams), NBT::TAG_String));
        $this->saveName($nbt);
    }
}