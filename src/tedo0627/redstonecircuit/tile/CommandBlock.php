<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\tile;

use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\event\server\CommandEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\BlockPowerHelper;
use tedo0627\redstonecircuit\block\CommandBlockType;
use tedo0627\redstonecircuit\block\inventory\CommandInventory;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\block\utils\AnyFacingOppositePlayerTrait;
use tedo0627\redstonecircuit\RedstoneCircuit;
use function array_map;
use function array_shift;
use function in_array;
use function is_bool;
use function preg_match_all;
use function stripslashes;

abstract class CommandBlock extends Spawnable implements Nameable, CommandSender{
    use NameableTrait;
    use PoweredByRedstoneTrait;
    use AnyFacingOppositePlayerTrait;
    use PermissibleDelegateTrait;

    public const TAG_LAST_OUTPUT_PARAMS = "LastOutputParams"; // TAG_LIST<TAG_STRING>
    public const TAG_AUTO = "auto"; // TAG_BYTE
    public const TAG_CONDITION_MET = "conditionMet"; // TAG_BYTE
    public const TAG_KEEP_PACKED = "keepPacked"; // TAG_BYTE
    public const TAG_LP_CONDIONAL_MODE = "LPCondionalMode"; // TAG_BYTE
    public const TAG_LP_REDSTONE_MODE = "LPRedstoneMode"; // TAG_BYTE
    public const TAG_POWERED = "powered"; // TAG_BYTE
    public const TAG_TRACK_OUTPUT = "TrackOutput"; // TAG_BYTE
    public const TAG_UPDATE_LAST_EXECUTION = "UpdateLastExecution"; // TAG_BYTE
    public const TAG_LP_COMMAND_MODE = "LPCommandMode"; // TAG_INT
    public const TAG_SUCCESS_COUNT = "SuccessCount"; // TAG_INT
    public const TAG_VERSION = "Version"; // TAG_INT
    public const TAG_COMMAND = "Command"; // TAG_STRING
    public const TAG_LAST_OUTPUT = "LastOutput"; // TAG_STRING

    protected CommandInventory $inventory;
    protected bool $auto = false;
    protected bool $conditionMet = false;
    protected string $command = "";
    //protected bool $keepPacked = false; // Java edition only
    protected string $lastOutput = "";
    /** @var string[] $lastOutputParams */
    protected array $lastOutputParams = [];
    protected bool $lpCondionalMode = false;
    protected int $lpCommandMode = 0;
    protected bool $lpRedstoneMode = false;
    protected bool $powered = false;
    protected int $successCount = 0;
    protected bool $trackOutput = true;
    //protected bool $updateLastExecution = true; // Java edition only
    protected int $version = 4;

    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);
        $this->perm = new PermissibleBase([DefaultPermissions::ROOT_OPERATOR => true]);
        $this->inventory = new CommandInventory($this->getPosition(), $this->getCommandBlockType());
    }

    public function getDefaultName() : string{
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

    protected function execute() : void{
        if($this->check() && $this->dispatch())
            ++$this->successCount;

        $block = $this->getBlock()->getSide($this->getFacing());
        if(!$block instanceof BlockCommand) return;
        if(!$this->getCommandBlockType()->equals(CommandBlockType::CHAIN())) return;

        $pos = $this->getPosition();
        $index = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        $block->chain([$index]);
    }

    protected function check() : bool{
        if($this->command === "") return false;

        if($this->conditionalMode){
            $tile = $this->getBlock()->getPosition()->getWorld()->getTile($this->getBlock()->getSide(Facing::opposite($this->getFacing()))->getPosition());
            if(!$tile instanceof CommandBlock) return false;
            if($tile->successCount <= 0) return false;
        }

        if($this->auto) return true;
        return BlockPowerHelper::isPowered($this->getBlock());
    }

    protected function chain(array $blockIndex = []) : void{
        $pos = $this->getPosition();
        $index = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        if(in_array($index, $blockIndex, true)) return;

        if($this->tickDelay !== 0){
            $this->tick = $this->tickDelay;
            return;
        }

        if($this->check() && $this->dispatch())
            ++$this->successCount;
        $block = $this->getBlock()->getSide($this->getFacing());
        if(!$block instanceof BlockCommand) return;
        if(!$this->getCommandBlockType()->equals(CommandBlockType::CHAIN())) return;

        $pos = $this->getPosition();
        $blockIndex[] = World::blockHash($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());
        $block->chain($blockIndex);
    }

    protected function dispatch() : bool{
        if(RedstoneCircuit::isCallEvent()){
            $event = new CommandEvent($this, $this->command);
            $event->call();
            if($event->isCancelled()) return false;

            $this->command = $event->getCommand();
        }

        $args = [];
        preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $this->command, $matches);
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
        if($sentCommandLabel !== null && ($target = Server::getInstance()->getCommandMap()->getCommand($sentCommandLabel)) !== null){
            $target->timings->startTiming();

            try{
                $result = $target->execute($this, $sentCommandLabel, $args);
                if(is_bool($result)) $successful = $result;
            }catch(InvalidCommandSyntaxException){
                $this->sendMessage($this->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage())));
            }finally{
                $target->timings->stopTiming();
            }
        }else{
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

        if($this->getCommandBlockType()->equals(CommandBlockType::REPEATING()))
            return true;

        if(!$this->getCommandBlockType()->equals(CommandBlockType::IMPULSE()))
            return false;

        if($this->tickDelay === 0){
            $this->execute();
        }else{
            $this->tick = $this->tickDelay;
        }

        if($this->tick > 0){
            if($this->getCommandBlockType()->equals(CommandBlockType::REPEATING()) && !$this->check()){
                $this->tick = -1;
                return true;
            }

            $this->tick--;
            if($this->tick === 1){
                $this->execute();
                if($this->getCommandBlockType()->equals(CommandBlockType::REPEATING()))
                    $this->tick = $this->tickDelay;
                return false;
            }
            return true;
        }

        if(!$this->getCommandBlockType()->equals(CommandBlockType::REPEATING()))
            return false;

        if($this->tickDelay === 0 || ($this->tick === -1 && $this->executeOnFirstTick)){
            $this->tick = 0;
            $this->execute();
        }
        $this->tick = $this->tickDelay;

        $this->timings->stopTiming();

        return false;
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->auto = $nbt->getByte(self::TAG_AUTO, 0) === 1;
        $this->conditionMet = $nbt->getByte(self::TAG_CONDITION_MET, 0) === 1;
        $this->command = $nbt->getString(self::TAG_COMMAND, "");
        $this->loadName($nbt);
        //$this->keepPacked = $nbt->getByte(self::TAG_KEEP_PACKED, 0) === 1; // Java edition only
        $this->lastOutput = $nbt->getString(self::TAG_LAST_OUTPUT, "");
        $this->lastOutputParams = $nbt->getListTag(self::TAG_LAST_OUTPUT_PARAMS)?->getAllValues() ?? [];
        $this->lpCondionalMode = $nbt->getByte(self::TAG_LP_CONDIONAL_MODE, 0) === 1;
        $this->lpCommandMode = $nbt->getInt(self::TAG_LP_COMMAND_MODE, 0);
        $this->lpRedstoneMode = $nbt->getByte(self::TAG_LP_REDSTONE_MODE, 0) === 1;
        $this->powered = $nbt->getByte(self::TAG_POWERED, 0) === 1;
        $this->successCount = $nbt->getInt(self::TAG_SUCCESS_COUNT, 0);
        $this->trackOutput = $nbt->getByte(self::TAG_TRACK_OUTPUT, 0) === 1 || $nbt->getByte(self::TAG_UPDATE_LAST_EXECUTION, 0) === 1;
        $this->version = $nbt->getInt(self::TAG_VERSION, 4);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $nbt->setByte(self::TAG_AUTO, $this->auto ? 1 : 0);
        $nbt->setByte(self::TAG_CONDITION_MET, $this->conditionMet ? 1 : 0);
        $nbt->setString(self::TAG_COMMAND, $this->command);
        //$nbt->setByte(self::TAG_KEEP_PACKED, $this->keepPacked ? 1 : 0); // Java edition only
        $nbt->setString(self::TAG_LAST_OUTPUT, $this->lastOutput);
        $nbt->setTag(self::TAG_LAST_OUTPUT_PARAMS, new ListTag(array_map(static fn(string $param) => new StringTag($param), $this->lastOutputParams), NBT::TAG_String));
        $nbt->setByte(self::TAG_LP_CONDIONAL_MODE, $this->lpCondionalMode ? 1 : 0);
        $nbt->setInt(self::TAG_LP_COMMAND_MODE, $this->lpCommandMode);
        $nbt->setByte(self::TAG_LP_REDSTONE_MODE, $this->lpRedstoneMode ? 1 : 0);
        $nbt->setByte(self::TAG_POWERED, $this->powered ? 1 : 0);
        $nbt->setInt(self::TAG_SUCCESS_COUNT, $this->successCount);
        $nbt->setByte(self::TAG_TRACK_OUTPUT, $this->trackOutput ? 1 : 0);
        $nbt->setInt(self::TAG_VERSION, $this->version);
    }

    public function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $this->writeSaveData($nbt);
    }
}
