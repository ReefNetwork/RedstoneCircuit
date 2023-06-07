<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\tile;

use BadMethodCallException;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\Server;
use pocketmine\utils\Limits;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use tedo0627\redstonecircuit\block\enums\CommandBlockType;
use tedo0627\redstonecircuit\block\inventory\CommandInventory;
use tedo0627\redstonecircuit\block\mechanism\BlockCommand;
use tedo0627\redstonecircuit\block\utils\AnyFacingOppositePlayerTrait;
use tedo0627\redstonecircuit\RedstoneCircuit;
use function array_map;
use function explode;
use function trim;
use const PHP_INT_MAX;

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
    protected bool $updateLastExecution = true; // not to be saved
    protected int $lastExecution = -1; // not to be saved
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

    abstract public function setCommandBlockType(CommandBlockType $type) : CommandBlock;

    protected function markConditionMet() : bool{
        $this->conditionMet = true;
        if($this->lpCondionalMode) {
            $this->conditionMet = $this->successCount > 0;
        }

        return $this->conditionMet;
    }

    protected function execute() : void{
        if($this->command !== ""){
            $this->performCommand();
        }else{
            $this->successCount = 0;
        }

        self::executeChain($this->getBlock()->getPosition(), $this->facing);
    }

    public function performCommand() : bool{
        if(Server::getInstance()->getTick() !== $this->lastExecution) {
            $this->successCount = 0;
            if($this->command !== "") {
                $this->lastOutput = "";
                try{
                    if(Server::getInstance()->dispatchCommand($this, $this->command, RedstoneCircuit::isCallEvent())) {
                        ++$this->successCount;
                    }
                }catch(InvalidCommandSyntaxException $e){
                    // TODO
                }
            }

            if($this->updateLastExecution) {
                $this->lastExecution = Server::getInstance()->getTick();
            }else{
                $this->lastExecution = -1;
            }

            return true;
        }

        return false;
    }

    protected static function executeChain(Position $pos, int $facing) : void{
        $world = $pos->getWorld();
        $block = $world->getBlock($pos);
        $i = Limits::INT32_MAX; // TODO: read game rules
        while(--$i > 0) {
            $block = $block->getSide($facing);
            if(!$block instanceof BlockCommand) {
                break;
            }
            $facing = $block->getFacing();

            $tile = $world->getTile($pos);
            if(!$tile instanceof CommandBlock || !$tile->getCommandBlockType()->equals(CommandBlockType::CHAIN())) {
                break;
            }

            if($tile->powered || $tile->auto) {
                if($tile->markConditionMet()) {
                    if(!$tile->performCommand()) {
                        break;
                    }
                }

                $block->onNearbyBlockChange();
            }elseif($tile->lpCondionalMode) {
                $tile->successCount = 0;
            }
        }

        if($i === 0) {
            Server::getInstance()->getLogger()->warning("Command Block chain tried to execute more than " . Limits::INT32_MAX . " blocks!");
        }
    }

    public function onUpdate() : bool{
        //TODO: move this to Block
        if($this->closed){
            return false;
        }

        $this->timings->startTiming();

        if($this->getCommandBlockType()->equals(CommandBlockType::REPEATING())){
            $this->markConditionMet();
            if($this->conditionMet) {
                $this->execute();
            }elseif($this->lpCondionalMode){
                $this->successCount = 0;
            }

            if($this->powered || $this->auto){
                return true;
            }
        }elseif($this->getCommandBlockType()->equals(CommandBlockType::IMPULSE())){
            if($this->conditionMet) {
                $this->execute();
            }elseif($this->lpCondionalMode){
                $this->successCount = 0;
            }
        }

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

    public function getLanguage() : Language{
        return Server::getInstance()->getLanguage();
    }

    public function sendMessage(Translatable|string $message) : void{
        if($message instanceof Translatable){
            $this->lastOutput = $message->getText();
            $this->lastOutputParams = array_map(
                static fn(string|Translatable $param) => $param instanceof Translatable ? $param->getText() : $param,
                $message->getParameters()
            );
            $message = $this->getLanguage()->translate($message);
        }

        foreach(explode("\n", trim($message)) as $line){
            Terminal::writeLine(TextFormat::GREEN . "CommandBlock output | " . TextFormat::addBase(TextFormat::WHITE, $line));
        }
    }

    public function getServer() : Server{
        return Server::getInstance();
    }

    public function getScreenLineHeight() : int{
        return PHP_INT_MAX;
    }

    public function setScreenLineHeight(?int $height) : void{
        throw new BadMethodCallException("Cannot set screen line height of command block");
    }
}
