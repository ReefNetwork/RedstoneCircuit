<?php

declare(strict_types=1);

namespace tedo0627\redstonecircuit\block\dispenser;

use InvalidArgumentException;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\entity\projectile\Snowball;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Item;
use pocketmine\utils\RegistryTrait;

/**
 * @generate-registry-docblock
 *
 * @method static ProjectileDispenseBehavior ARROW()
 * @method static BoneMealDispenseBehavior BONE_MEAL()
 * @method static BucketDispenseBehavior BUCKET()
 * @method static ArmorDispenseBehavior CARVED_PUMPKIN()
 * @method static ArmorDispenseBehavior CHAIN_BOOTS()
 * @method static ArmorDispenseBehavior CHAIN_CHESTPLATE()
 * @method static ArmorDispenseBehavior CHAIN_HELMET()
 * @method static ArmorDispenseBehavior CHAIN_LEGGINGS()
 * @method static DefaultItemDispenseBehavior DEFAULT()
 * @method static ArmorDispenseBehavior DIAMOND_BOOTS()
 * @method static ArmorDispenseBehavior DIAMOND_CHESTPLATE()
 * @method static ArmorDispenseBehavior DIAMOND_HELMET()
 * @method static ArmorDispenseBehavior DIAMOND_LEGGINGS()
 * @method static ProjectileDispenseBehavior EGG()
 * @method static ProjectileDispenseBehavior EXPERIENCE_BOTTLE()
 * @method static FlintSteelDispenseBehavior FLINT_AND_STEEL()
 * @method static GlassBottleDispenseBehavior GLASS_BOTTLE()
 * @method static ArmorDispenseBehavior GOLD_BOOTS()
 * @method static ArmorDispenseBehavior GOLD_CHESTPLATE()
 * @method static ArmorDispenseBehavior GOLD_HELMET()
 * @method static ArmorDispenseBehavior GOLD_LEGGINGS()
 * @method static ArmorDispenseBehavior IRON_BOOTS()
 * @method static ArmorDispenseBehavior IRON_CHESTPLATE()
 * @method static ArmorDispenseBehavior IRON_HELMET()
 * @method static ArmorDispenseBehavior IRON_LEGGINGS()
 * @method static ArmorDispenseBehavior LEATHER_BOOTS()
 * @method static ArmorDispenseBehavior LEATHER_CAP()
 * @method static ArmorDispenseBehavior LEATHER_PANTS()
 * @method static ArmorDispenseBehavior LEATHER_TUNIC()
 * @method static ArmorDispenseBehavior MOB_HEAD()
 * @method static ShulkerBoxDispenseBehavior SHULKER_BOX()
 * @method static ProjectileDispenseBehavior SNOWBALL()
 * @method static TNTDispenseBehavior TNT()
 * @method static ArmorDispenseBehavior TURTLE_HELMET()
 */
final class DispenserBehaviorRegistry{
    use RegistryTrait;

    private function __construct(){
        //NOOP
    }

    protected static function register(string $name, DispenseItemBehavior $block) : void{
        self::_registryRegister($name, $block);
    }

    /**
     * @return DispenseItemBehavior[]
     * @phpstan-return array<string, DispenseItemBehavior>
     */
    public static function getAll() : array{
        //phpstan doesn't support generic traits yet :(
        /** @var DispenseItemBehavior[] $result */
        $result = self::_registryGetAll();
        return $result;
    }

    protected static function setup() : void{
        self::register("default", new DefaultItemDispenseBehavior());
        self::register("bucket", new BucketDispenseBehavior());
        self::register("flint_and_steel", new FlintSteelDispenseBehavior());
        self::register("bone_meal", new BoneMealDispenseBehavior());
        self::register("tnt", new TNTDispenseBehavior());
        self::register("shulker_box", new ShulkerBoxDispenseBehavior());
        self::register("glass_bottle", new GlassBottleDispenseBehavior());
        self::register("arrow", new class extends ProjectileDispenseBehavior{
            public function getEntity(Location $location, Item $item) : Entity{
                return new Arrow($location, null, false);
            }
        });
        self::register("egg", new class extends ProjectileDispenseBehavior{
            public function getEntity(Location $location, Item $item) : Entity{
                return new Egg($location, null);
            }
        });
        self::register("snowball", new class extends ProjectileDispenseBehavior{
            public function getEntity(Location $location, Item $item) : Entity{
                return new Snowball($location, null);
            }
        });
        self::register("expereince_bottle", new class extends ProjectileDispenseBehavior{
            public function getEntity(Location $location, Item $item) : Entity{
                return new ExperienceBottle($location, null);
            }
        });
        self::register("splash_potion", new class extends ProjectileDispenseBehavior{
            public function getEntity(Location $location, Item $item) : Entity{
                if(!$item instanceof \pocketmine\item\SplashPotion) throw new InvalidArgumentException("item was not SplashPotion");
                return new SplashPotion($location, null, $item->getType());
            }
        });
        foreach([
            "leather_cap" => ArmorInventory::SLOT_HEAD,
            "leather_tunic" => ArmorInventory::SLOT_CHEST,
            "leather_pants" => ArmorInventory::SLOT_LEGS,
            "leather_boots" => ArmorInventory::SLOT_FEET,
            "chainmail_helmet" => ArmorInventory::SLOT_HEAD,
            "chainmail_chestplate" => ArmorInventory::SLOT_CHEST,
            "chainmail_leggings" => ArmorInventory::SLOT_LEGS,
            "chainmail_boots" => ArmorInventory::SLOT_FEET,
            "iron_helmet" => ArmorInventory::SLOT_HEAD,
            "iron_chestplate" => ArmorInventory::SLOT_CHEST,
            "iron_leggings" => ArmorInventory::SLOT_LEGS,
            "iron_boots" => ArmorInventory::SLOT_FEET,
            "diamond_helmet" => ArmorInventory::SLOT_HEAD,
            "diamond_chestplate" => ArmorInventory::SLOT_CHEST,
            "diamond_leggings" => ArmorInventory::SLOT_LEGS,
            "diamond_boots" => ArmorInventory::SLOT_FEET,
            "golden_helmet" => ArmorInventory::SLOT_HEAD,
            "golden_chestplate" => ArmorInventory::SLOT_CHEST,
            "golden_leggings" => ArmorInventory::SLOT_LEGS,
            "golden_boots" => ArmorInventory::SLOT_FEET,
            "carved_pumpkin" => ArmorInventory::SLOT_HEAD,
            "mob_head" => ArmorInventory::SLOT_HEAD,
            "turtle_helmet" => ArmorInventory::SLOT_HEAD,
            "netherite_helmet" => ArmorInventory::SLOT_HEAD,
            "netherite_chestplate" => ArmorInventory::SLOT_CHEST,
            "netherite_leggings" => ArmorInventory::SLOT_LEGS,
            "netherite_boots" => ArmorInventory::SLOT_FEET,
            // TODO: Add Elytra
        ] as $item_name => $slot){
            self::register($item_name, new ArmorDispenseBehavior($slot));
        }
    }
}
