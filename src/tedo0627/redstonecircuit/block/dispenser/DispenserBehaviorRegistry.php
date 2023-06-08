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
use pocketmine\item\Item;
use pocketmine\utils\RegistryTrait;

/**
 * @generate-registry-docblock
 *
 * @method static DefaultItemDispenseBehavior DEFAULT()
 * @method static BucketDispenseBehavior BUCKET()
 * @method static FlintSteelDispenseBehavior FLINT_AND_STEEL()
 * @method static BoneMealDispenseBehavior BONE_MEAL()
 * @method static TNTDispenseBehavior TNT()
 * @method static ShulkerBoxDispenseBehavior SHULKER_BOX()
 * @method static GlassBottleDispenseBehavior GLASS_BOTTLE()
 * @method static ProjectileDispenseBehavior ARROW()
 * @method static ProjectileDispenseBehavior EGG()
 * @method static ProjectileDispenseBehavior SNOWBALL()
 * @method static ProjectileDispenseBehavior EXPERIENCE_BOTTLE()
 * @method static ArmorDispenseBehavior LEATHER_CAP()
 * @method static ArmorDispenseBehavior LEATHER_TUNIC()
 * @method static ArmorDispenseBehavior LEATHER_PANTS()
 * @method static ArmorDispenseBehavior LEATHER_BOOTS()
 * @method static ArmorDispenseBehavior CHAIN_HELMET()
 * @method static ArmorDispenseBehavior CHAIN_CHESTPLATE()
 * @method static ArmorDispenseBehavior CHAIN_LEGGINGS()
 * @method static ArmorDispenseBehavior CHAIN_BOOTS()
 * @method static ArmorDispenseBehavior IRON_HELMET()
 * @method static ArmorDispenseBehavior IRON_CHESTPLATE()
 * @method static ArmorDispenseBehavior IRON_LEGGINGS()
 * @method static ArmorDispenseBehavior IRON_BOOTS()
 * @method static ArmorDispenseBehavior DIAMOND_HELMET()
 * @method static ArmorDispenseBehavior DIAMOND_CHESTPLATE()
 * @method static ArmorDispenseBehavior DIAMOND_LEGGINGS()
 * @method static ArmorDispenseBehavior DIAMOND_BOOTS()
 * @method static ArmorDispenseBehavior GOLD_HELMET()
 * @method static ArmorDispenseBehavior GOLD_CHESTPLATE()
 * @method static ArmorDispenseBehavior GOLD_LEGGINGS()
 * @method static ArmorDispenseBehavior GOLD_BOOTS()
 * @method static ArmorDispenseBehavior TURTLE_HELMET()
 * @method static ArmorDispenseBehavior CARVED_PUMPKIN()
 * @method static ArmorDispenseBehavior MOB_HEAD()
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
    }
}
