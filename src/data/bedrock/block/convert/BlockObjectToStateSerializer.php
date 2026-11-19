<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace wolfcore\data\bedrock\block\convert;

use wolfcore\block\ActivatorRail;
use wolfcore\block\Anvil;
use wolfcore\block\Bamboo;
use wolfcore\block\BambooSapling;
use wolfcore\block\Barrel;
use wolfcore\block\Bed;
use wolfcore\block\Beetroot;
use wolfcore\block\Bell;
use wolfcore\block\Block;
use wolfcore\block\BoneBlock;
use wolfcore\block\BrewingStand;
use wolfcore\block\BrownMushroomBlock;
use wolfcore\block\Button;
use wolfcore\block\Cactus;
use wolfcore\block\Cake;
use wolfcore\block\CakeWithCandle;
use wolfcore\block\CakeWithDyedCandle;
use wolfcore\block\Candle;
use wolfcore\block\Carpet;
use wolfcore\block\Carrot;
use wolfcore\block\CarvedPumpkin;
use wolfcore\block\CaveVines;
use wolfcore\block\Chain;
use wolfcore\block\Chest;
use wolfcore\block\ChorusFlower;
use wolfcore\block\CocoaBlock;
use wolfcore\block\Concrete;
use wolfcore\block\ConcretePowder;
use wolfcore\block\Copper;
use wolfcore\block\CopperSlab;
use wolfcore\block\CopperStairs;
use wolfcore\block\Coral;
use wolfcore\block\CoralBlock;
use wolfcore\block\DaylightSensor;
use wolfcore\block\DetectorRail;
use wolfcore\block\Dirt;
use wolfcore\block\Door;
use wolfcore\block\DoublePlant;
use wolfcore\block\DoubleTallGrass;
use wolfcore\block\DyedCandle;
use wolfcore\block\DyedShulkerBox;
use wolfcore\block\EnderChest;
use wolfcore\block\EndPortalFrame;
use wolfcore\block\EndRod;
use wolfcore\block\Farmland;
use wolfcore\block\FenceGate;
use wolfcore\block\FillableCauldron;
use wolfcore\block\Fire;
use wolfcore\block\FloorBanner;
use wolfcore\block\FloorCoralFan;
use wolfcore\block\FloorSign;
use wolfcore\block\Froglight;
use wolfcore\block\FrostedIce;
use wolfcore\block\Furnace;
use wolfcore\block\GlazedTerracotta;
use wolfcore\block\GlowLichen;
use wolfcore\block\HayBale;
use wolfcore\block\HangingSign;
use wolfcore\block\Hopper;
use wolfcore\block\ItemFrame;
use wolfcore\block\Ladder;
use wolfcore\block\Lantern;
use wolfcore\block\Lava;
use wolfcore\block\Leaves;
use wolfcore\block\Lectern;
use wolfcore\block\Lever;
use wolfcore\block\Light;
use wolfcore\block\LightningRod;
use wolfcore\block\LitPumpkin;
use wolfcore\block\Loom;
use wolfcore\block\MelonStem;
use wolfcore\block\MobHead;
use wolfcore\block\NetherPortal;
use wolfcore\block\NetherVines;
use wolfcore\block\NetherWartPlant;
use wolfcore\block\Potato;
use wolfcore\block\Piston;
use wolfcore\block\PistonHead;
use wolfcore\block\PoweredRail;
use wolfcore\block\PumpkinStem;
use wolfcore\block\Rail;
use wolfcore\block\RedMushroomBlock;
use wolfcore\block\RedstoneComparator;
use wolfcore\block\RedstoneLamp;
use wolfcore\block\RedstoneOre;
use wolfcore\block\RedstoneRepeater;
use wolfcore\block\RedstoneTorch;
use wolfcore\block\RedstoneWire;
use wolfcore\block\RuntimeBlockStateRegistry;
use wolfcore\block\Sapling;
use wolfcore\block\SeaPickle;
use wolfcore\block\SimplePillar;
use wolfcore\block\SimplePressurePlate;
use wolfcore\block\Slab;
use wolfcore\block\SnowLayer;
use wolfcore\block\Sponge;
use wolfcore\block\StainedGlass;
use wolfcore\block\StainedGlassPane;
use wolfcore\block\StainedHardenedClay;
use wolfcore\block\StainedHardenedGlass;
use wolfcore\block\StainedHardenedGlassPane;
use wolfcore\block\Stair;
use wolfcore\block\StoneButton;
use wolfcore\block\Stonecutter;
use wolfcore\block\StonePressurePlate;
use wolfcore\block\Sugarcane;
use wolfcore\block\SweetBerryBush;
use wolfcore\block\TNT;
use wolfcore\block\Torch;
use wolfcore\block\Trapdoor;
use wolfcore\block\TrappedChest;
use wolfcore\block\Tripwire;
use wolfcore\block\TripwireHook;
use wolfcore\block\utils\BrewingStandSlot;
use wolfcore\block\utils\CoralType;
use wolfcore\block\utils\DirtType;
use wolfcore\block\utils\DyeColor;
use wolfcore\block\utils\FroglightType;
use wolfcore\block\utils\LeverFacing;
use wolfcore\block\VanillaBlocks as Blocks;
use wolfcore\block\Vine;
use wolfcore\block\Wall;
use wolfcore\block\WallBanner;
use wolfcore\block\WallCoralFan;
use wolfcore\block\WallSign;
use wolfcore\block\Water;
use wolfcore\block\WeightedPressurePlateHeavy;
use wolfcore\block\WeightedPressurePlateLight;
use wolfcore\block\Wheat;
use wolfcore\block\Wood;
use wolfcore\block\WoodenButton;
use wolfcore\block\WoodenDoor;
use wolfcore\block\WoodenPressurePlate;
use wolfcore\block\WoodenStairs;
use wolfcore\block\WoodenTrapdoor;
use wolfcore\block\Wool;
use wolfcore\data\bedrock\block\BlockLegacyMetadata;
use wolfcore\data\bedrock\block\BlockStateData;
use wolfcore\data\bedrock\block\BlockStateNames as StateNames;
use wolfcore\data\bedrock\block\BlockStateSerializeException;
use wolfcore\data\bedrock\block\BlockStateSerializer;
use wolfcore\data\bedrock\block\BlockStateStringValues as StringValues;
use wolfcore\data\bedrock\block\BlockTypeNames as Ids;
use wolfcore\data\bedrock\block\convert\BlockStateSerializerHelper as Helper;
use wolfcore\data\bedrock\block\convert\BlockStateWriter as Writer;
use wolfcore\math\Axis;
use wolfcore\math\Facing;
use wolfcore\utils\AssumptionFailedError;
use function get_class;

final class BlockObjectToStateSerializer implements BlockStateSerializer{
	/**
	 * These callables actually accept Block, but for the sake of type completeness, it has to be never, since we can't
	 * describe the bottom type of a type hierarchy only containing Block.
	 *
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure(never) : Writer>
	 */
	private array $serializers = [];

	/**
	 * @var BlockStateData[]
	 * @phpstan-var array<int, BlockStateData>
	 */
	private array $cache = [];

	public function __construct(){
		$this->registerCandleSerializers();
		$this->registerFlatColorBlockSerializers();
		$this->registerFlatCoralSerializers();
		$this->registerCauldronSerializers();
		$this->registerFlatWoodBlockSerializers();
		$this->registerLegacyWoodBlockSerializers();
		$this->registerLeavesSerializers();
		$this->registerSimpleSerializers();
		$this->registerSerializers();
	}

	public function serialize(int $stateId) : BlockStateData{
		//TODO: singleton usage not ideal
		//TODO: we may want to deduplicate cache entries to avoid wasting memory
		return $this->cache[$stateId] ??= $this->serializeBlock(RuntimeBlockStateRegistry::getInstance()->fromStateId($stateId));
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 * @phpstan-param \Closure(TBlockType) : Writer $serializer
	 */
	public function map(Block $block, \Closure $serializer) : void{
		if(isset($this->serializers[$block->getTypeId()])){
			throw new \InvalidArgumentException("Block type ID " . $block->getTypeId() . " already has a serializer registered");
		}
		$this->serializers[$block->getTypeId()] = $serializer;
	}

	public function mapSimple(Block $block, string $id) : void{
		$this->map($block, fn() => Writer::create($id));
	}

	public function mapSlab(Slab $block, string $singleId, string $doubleId) : void{
		$this->map($block, fn(Slab $block) => Helper::encodeSlab($block, $singleId, $doubleId));
	}

	public function mapStairs(Stair $block, string $id) : void{
		$this->map($block, fn(Stair $block) => Helper::encodeStairs($block, Writer::create($id)));
	}

	public function mapLog(Wood $block, string $unstrippedId, string $strippedId) : void{
		$this->map($block, fn(Wood $block) => Helper::encodeLog($block, $unstrippedId, $strippedId));
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $blockState
	 *
	 * @throws BlockStateSerializeException
	 */
	public function serializeBlock(Block $blockState) : BlockStateData{
		$typeId = $blockState->getTypeId();

		$locatedSerializer = $this->serializers[$typeId] ?? null;
		if($locatedSerializer === null){
			throw new BlockStateSerializeException("No serializer registered for " . get_class($blockState) . " with type ID $typeId");
		}

		/**
		 * TODO: there is no guarantee that this type actually matches that of $blockState - a plugin may have stolen
		 * the type ID of the block (which never makes sense, even in a world where overriding block types is a thing).
		 * In the future we'll need some way to guarantee that type IDs are never reused (perhaps spl_object_id()?)
		 *
		 * @var \Closure $serializer
		 * @phpstan-var \Closure(TBlockType) : Writer $serializer
		 */
		$serializer = $locatedSerializer;

		/** @var Writer $writer */
		$writer = $serializer($blockState);
		return $writer->getBlockStateData();
	}

	private function registerCandleSerializers() : void{
		$this->map(Blocks::CANDLE(), fn(Candle $block) => Helper::encodeCandle($block, new Writer(Ids::CANDLE)));
		$this->map(Blocks::DYED_CANDLE(), fn(DyedCandle $block) => Helper::encodeCandle($block, new Writer(match($block->getColor()){
			DyeColor::BLACK() => Ids::BLACK_CANDLE,
			DyeColor::BLUE() => Ids::BLUE_CANDLE,
			DyeColor::BROWN() => Ids::BROWN_CANDLE,
			DyeColor::CYAN() => Ids::CYAN_CANDLE,
			DyeColor::GRAY() => Ids::GRAY_CANDLE,
			DyeColor::GREEN() => Ids::GREEN_CANDLE,
			DyeColor::LIGHT_BLUE() => Ids::LIGHT_BLUE_CANDLE,
			DyeColor::LIGHT_GRAY() => Ids::LIGHT_GRAY_CANDLE,
			DyeColor::LIME() => Ids::LIME_CANDLE,
			DyeColor::MAGENTA() => Ids::MAGENTA_CANDLE,
			DyeColor::ORANGE() => Ids::ORANGE_CANDLE,
			DyeColor::PINK() => Ids::PINK_CANDLE,
			DyeColor::PURPLE() => Ids::PURPLE_CANDLE,
			DyeColor::RED() => Ids::RED_CANDLE,
			DyeColor::WHITE() => Ids::WHITE_CANDLE,
			DyeColor::YELLOW() => Ids::YELLOW_CANDLE,
			default => throw new AssumptionFailedError("Unhandled DyeColor " . $block->getColor()->name())
		})));
		$this->map(Blocks::CAKE_WITH_CANDLE(), fn(CakeWithCandle $block) => Writer::create(Ids::CANDLE_CAKE)
			->writeBool(StateNames::LIT, $block->isLit()));
		$this->map(Blocks::CAKE_WITH_DYED_CANDLE(), fn(CakeWithDyedCandle $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK() => Ids::BLACK_CANDLE_CAKE,
			DyeColor::BLUE() => Ids::BLUE_CANDLE_CAKE,
			DyeColor::BROWN() => Ids::BROWN_CANDLE_CAKE,
			DyeColor::CYAN() => Ids::CYAN_CANDLE_CAKE,
			DyeColor::GRAY() => Ids::GRAY_CANDLE_CAKE,
			DyeColor::GREEN() => Ids::GREEN_CANDLE_CAKE,
			DyeColor::LIGHT_BLUE() => Ids::LIGHT_BLUE_CANDLE_CAKE,
			DyeColor::LIGHT_GRAY() => Ids::LIGHT_GRAY_CANDLE_CAKE,
			DyeColor::LIME() => Ids::LIME_CANDLE_CAKE,
			DyeColor::MAGENTA() => Ids::MAGENTA_CANDLE_CAKE,
			DyeColor::ORANGE() => Ids::ORANGE_CANDLE_CAKE,
			DyeColor::PINK() => Ids::PINK_CANDLE_CAKE,
			DyeColor::PURPLE() => Ids::PURPLE_CANDLE_CAKE,
			DyeColor::RED() => Ids::RED_CANDLE_CAKE,
			DyeColor::WHITE() => Ids::WHITE_CANDLE_CAKE,
			DyeColor::YELLOW() => Ids::YELLOW_CANDLE_CAKE,
			default => throw new AssumptionFailedError("Unhandled DyeColor " . $block->getColor()->name())
		})->writeBool(StateNames::LIT, $block->isLit()));
	}

	public function registerFlatColorBlockSerializers() : void{
		$this->map(Blocks::GLAZED_TERRACOTTA(), function(GlazedTerracotta $block) : Writer{
			return Writer::create(match($color = $block->getColor()){
				DyeColor::BLACK() => Ids::BLACK_GLAZED_TERRACOTTA,
				DyeColor::BLUE() => Ids::BLUE_GLAZED_TERRACOTTA,
				DyeColor::BROWN() => Ids::BROWN_GLAZED_TERRACOTTA,
				DyeColor::CYAN() => Ids::CYAN_GLAZED_TERRACOTTA,
				DyeColor::GRAY() => Ids::GRAY_GLAZED_TERRACOTTA,
				DyeColor::GREEN() => Ids::GREEN_GLAZED_TERRACOTTA,
				DyeColor::LIGHT_BLUE() => Ids::LIGHT_BLUE_GLAZED_TERRACOTTA,
				DyeColor::LIGHT_GRAY() => Ids::SILVER_GLAZED_TERRACOTTA,
				DyeColor::LIME() => Ids::LIME_GLAZED_TERRACOTTA,
				DyeColor::MAGENTA() => Ids::MAGENTA_GLAZED_TERRACOTTA,
				DyeColor::ORANGE() => Ids::ORANGE_GLAZED_TERRACOTTA,
				DyeColor::PINK() => Ids::PINK_GLAZED_TERRACOTTA,
				DyeColor::PURPLE() => Ids::PURPLE_GLAZED_TERRACOTTA,
				DyeColor::RED() => Ids::RED_GLAZED_TERRACOTTA,
				DyeColor::WHITE() => Ids::WHITE_GLAZED_TERRACOTTA,
				DyeColor::YELLOW() => Ids::YELLOW_GLAZED_TERRACOTTA,
				default => throw new AssumptionFailedError("Unhandled dye colour " . $color->name())
			})
				->writeHorizontalFacing($block->getFacing());
		});

		$this->map(Blocks::WOOL(), fn(Wool $block) => Writer::create(match($color = $block->getColor()){
			DyeColor::BLACK() => Ids::BLACK_WOOL,
			DyeColor::BLUE() => Ids::BLUE_WOOL,
			DyeColor::BROWN() => Ids::BROWN_WOOL,
			DyeColor::CYAN() => Ids::CYAN_WOOL,
			DyeColor::GRAY() => Ids::GRAY_WOOL,
			DyeColor::GREEN() => Ids::GREEN_WOOL,
			DyeColor::LIGHT_BLUE() => Ids::LIGHT_BLUE_WOOL,
			DyeColor::LIGHT_GRAY() => Ids::LIGHT_GRAY_WOOL,
			DyeColor::LIME() => Ids::LIME_WOOL,
			DyeColor::MAGENTA() => Ids::MAGENTA_WOOL,
			DyeColor::ORANGE() => Ids::ORANGE_WOOL,
			DyeColor::PINK() => Ids::PINK_WOOL,
			DyeColor::PURPLE() => Ids::PURPLE_WOOL,
			DyeColor::RED() => Ids::RED_WOOL,
			DyeColor::WHITE() => Ids::WHITE_WOOL,
			DyeColor::YELLOW() => Ids::YELLOW_WOOL,
			default => throw new AssumptionFailedError("Unhandled dye colour " . $color->name())
		}));

		$this->map(Blocks::CARPET(), fn(Carpet $block) => Writer::create(match($color = $block->getColor()){
			DyeColor::BLACK() => Ids::BLACK_CARPET,
			DyeColor::BLUE() => Ids::BLUE_CARPET,
			DyeColor::BROWN() => Ids::BROWN_CARPET,
			DyeColor::CYAN() => Ids::CYAN_CARPET,
			DyeColor::GRAY() => Ids::GRAY_CARPET,
			DyeColor::GREEN() => Ids::GREEN_CARPET,
			DyeColor::LIGHT_BLUE() => Ids::LIGHT_BLUE_CARPET,
			DyeColor::LIGHT_GRAY() => Ids::LIGHT_GRAY_CARPET,
			DyeColor::LIME() => Ids::LIME_CARPET,
			DyeColor::MAGENTA() => Ids::MAGENTA_CARPET,
			DyeColor::ORANGE() => Ids::ORANGE_CARPET,
			DyeColor::PINK() => Ids::PINK_CARPET,
			DyeColor::PURPLE() => Ids::PURPLE_CARPET,
			DyeColor::RED() => Ids::RED_CARPET,
			DyeColor::WHITE() => Ids::WHITE_CARPET,
			DyeColor::YELLOW() => Ids::YELLOW_CARPET,
			default => throw new AssumptionFailedError("Unhandled dye colour " . $color->name())
		}));
	}

	private function registerFlatCoralSerializers() : void{
		$this->map(Blocks::CORAL(), fn(Coral $block) => Writer::create(
			match($coralType = $block->getCoralType()){
				CoralType::BRAIN() => $block->isDead() ? Ids::DEAD_BRAIN_CORAL : Ids::BRAIN_CORAL,
				CoralType::BUBBLE() => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL : Ids::BUBBLE_CORAL,
				CoralType::FIRE() => $block->isDead() ? Ids::DEAD_FIRE_CORAL : Ids::FIRE_CORAL,
				CoralType::HORN() => $block->isDead() ? Ids::DEAD_HORN_CORAL : Ids::HORN_CORAL,
				CoralType::TUBE() => $block->isDead() ? Ids::DEAD_TUBE_CORAL : Ids::TUBE_CORAL,
				default => throw new AssumptionFailedError("Unhandled coral type " . $coralType->name())
			}
		));
	}

	private function registerCauldronSerializers() : void{
		$this->map(Blocks::CAULDRON(), fn() => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, 0));
		$this->map(Blocks::LAVA_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_LAVA, $b->getFillLevel()));
		//potion cauldrons store their real information in the block actor data
		$this->map(Blocks::POTION_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
		$this->map(Blocks::WATER_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
	}

	private function registerFlatWoodBlockSerializers() : void{
		$this->mapLog(Blocks::BAMBOO_BLOCK(), Ids::BAMBOO_BLOCK, Ids::STRIPPED_BAMBOO_BLOCK);
		$this->mapSimple(Blocks::BAMBOO_PLANKS(), Ids::BAMBOO_PLANKS);
		$this->mapSimple(Blocks::BAMBOO_MOSAIC(), Ids::BAMBOO_MOSAIC);
		$this->map(Blocks::BAMBOO_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::BAMBOO_BUTTON)));
		$this->map(Blocks::BAMBOO_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::BAMBOO_DOOR)));
		$this->mapSimple(Blocks::BAMBOO_FENCE(), Ids::BAMBOO_FENCE);
		$this->map(Blocks::BAMBOO_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::BAMBOO_FENCE_GATE)));
		$this->map(Blocks::BAMBOO_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::BAMBOO_PRESSURE_PLATE)));
		$this->map(Blocks::BAMBOO_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::BAMBOO_TRAPDOOR)));
		$this->mapSlab(Blocks::BAMBOO_SLAB(), Ids::BAMBOO_SLAB, Ids::BAMBOO_DOUBLE_SLAB);
		$this->mapStairs(Blocks::BAMBOO_STAIRS(), Ids::BAMBOO_STAIRS);
		$this->mapSlab(Blocks::BAMBOO_MOSAIC_SLAB(), Ids::BAMBOO_MOSAIC_SLAB, Ids::BAMBOO_MOSAIC_DOUBLE_SLAB);
		$this->mapStairs(Blocks::BAMBOO_MOSAIC_STAIRS(), Ids::BAMBOO_MOSAIC_STAIRS);
		$this->map(Blocks::BAMBOO_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::BAMBOO_STANDING_SIGN)));
		$this->map(Blocks::BAMBOO_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::BAMBOO_WALL_SIGN)));
		$this->map(Blocks::BAMBOO_HANGING_SIGN(), fn(HangingSign $block) => Writer::create(Ids::BAMBOO_HANGING_SIGN)
			->writeBool(StateNames::ATTACHED_BIT, $block->isAttached())
			->writeFacingDirection($block->getFacing())
			->writeInt(StateNames::GROUND_SIGN_DIRECTION, $block->getRotation())
			->writeBool(StateNames::HANGING, $block->isHanging()));

		$this->map(Blocks::ACACIA_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::ACACIA_BUTTON)));
		$this->map(Blocks::ACACIA_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::ACACIA_DOOR)));
		$this->map(Blocks::ACACIA_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::ACACIA_FENCE_GATE)));
		$this->map(Blocks::ACACIA_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::ACACIA_PRESSURE_PLATE)));
		$this->map(Blocks::ACACIA_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::ACACIA_STANDING_SIGN)));
		$this->map(Blocks::ACACIA_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::ACACIA_STAIRS)));
		$this->map(Blocks::ACACIA_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::ACACIA_TRAPDOOR)));
		$this->map(Blocks::ACACIA_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::ACACIA_WALL_SIGN)));
		$this->mapLog(Blocks::ACACIA_LOG(), Ids::ACACIA_LOG, Ids::STRIPPED_ACACIA_LOG);
		$this->mapSimple(Blocks::ACACIA_FENCE(), Ids::ACACIA_FENCE);
		//wood, planks and slabs still use the old way of storing wood type

		$this->map(Blocks::BIRCH_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::BIRCH_BUTTON)));
		$this->map(Blocks::BIRCH_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::BIRCH_DOOR)));
		$this->map(Blocks::BIRCH_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::BIRCH_FENCE_GATE)));
		$this->map(Blocks::BIRCH_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::BIRCH_PRESSURE_PLATE)));
		$this->map(Blocks::BIRCH_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::BIRCH_STANDING_SIGN)));
		$this->map(Blocks::BIRCH_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::BIRCH_TRAPDOOR)));
		$this->map(Blocks::BIRCH_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::BIRCH_WALL_SIGN)));
		$this->mapLog(Blocks::BIRCH_LOG(), Ids::BIRCH_LOG, Ids::STRIPPED_BIRCH_LOG);
		$this->mapSimple(Blocks::BIRCH_FENCE(), Ids::BIRCH_FENCE);
		$this->mapStairs(Blocks::BIRCH_STAIRS(), Ids::BIRCH_STAIRS);
		//wood, planks and slabs still use the old way of storing wood type

		$this->map(Blocks::CHERRY_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::CHERRY_BUTTON)));
		$this->map(Blocks::CHERRY_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::CHERRY_DOOR)));
		$this->map(Blocks::CHERRY_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::CHERRY_FENCE_GATE)));
		$this->map(Blocks::CHERRY_LOG(), fn(Wood $block) => Helper::encodeLog($block, Ids::CHERRY_LOG, Ids::STRIPPED_CHERRY_LOG));
		$this->map(Blocks::CHERRY_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::CHERRY_PRESSURE_PLATE)));
		$this->map(Blocks::CHERRY_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::CHERRY_STANDING_SIGN)));
		$this->map(Blocks::CHERRY_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::CHERRY_TRAPDOOR)));
		$this->map(Blocks::CHERRY_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::CHERRY_WALL_SIGN)));
		$this->mapSimple(Blocks::CHERRY_FENCE(), Ids::CHERRY_FENCE);
		$this->mapSimple(Blocks::CHERRY_PLANKS(), Ids::CHERRY_PLANKS);
		$this->mapSlab(Blocks::CHERRY_SLAB(), Ids::CHERRY_SLAB, Ids::CHERRY_DOUBLE_SLAB);
		$this->mapStairs(Blocks::CHERRY_STAIRS(), Ids::CHERRY_STAIRS);
		$this->map(Blocks::CHERRY_WOOD(), function(Wood $block) : Writer{
			//we can't use the standard method for this because cherry_wood has a useless property attached to it
			if(!$block->isStripped()){
				return Writer::create(Ids::CHERRY_WOOD)
					->writePillarAxis($block->getAxis())
					->writeBool(StateNames::STRIPPED_BIT, false); //this is useless, but it has to be written
			}else{
				return Writer::create(Ids::STRIPPED_CHERRY_WOOD)
					->writePillarAxis($block->getAxis());
			}
		});

		$this->map(Blocks::CRIMSON_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::CRIMSON_BUTTON)));
		$this->map(Blocks::CRIMSON_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::CRIMSON_DOOR)));
		$this->map(Blocks::CRIMSON_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::CRIMSON_FENCE_GATE)));
		$this->map(Blocks::CRIMSON_HYPHAE(), fn(Wood $block) => Helper::encodeLog($block, Ids::CRIMSON_HYPHAE, Ids::STRIPPED_CRIMSON_HYPHAE));
		$this->map(Blocks::CRIMSON_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::CRIMSON_PRESSURE_PLATE)));
		$this->map(Blocks::CRIMSON_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::CRIMSON_STANDING_SIGN)));
		$this->map(Blocks::CRIMSON_STEM(), fn(Wood $block) => Helper::encodeLog($block, Ids::CRIMSON_STEM, Ids::STRIPPED_CRIMSON_STEM));
		$this->map(Blocks::CRIMSON_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::CRIMSON_TRAPDOOR)));
		$this->map(Blocks::CRIMSON_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::CRIMSON_WALL_SIGN)));
		$this->mapSimple(Blocks::CRIMSON_FENCE(), Ids::CRIMSON_FENCE);
		$this->mapSimple(Blocks::CRIMSON_PLANKS(), Ids::CRIMSON_PLANKS);
		$this->mapSlab(Blocks::CRIMSON_SLAB(), Ids::CRIMSON_SLAB, Ids::CRIMSON_DOUBLE_SLAB);
		$this->mapStairs(Blocks::CRIMSON_STAIRS(), Ids::CRIMSON_STAIRS);

		$this->map(Blocks::DARK_OAK_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::DARK_OAK_BUTTON)));
		$this->map(Blocks::DARK_OAK_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::DARK_OAK_DOOR)));
		$this->map(Blocks::DARK_OAK_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::DARK_OAK_FENCE_GATE)));
		$this->map(Blocks::DARK_OAK_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::DARK_OAK_PRESSURE_PLATE)));
		$this->map(Blocks::DARK_OAK_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::DARKOAK_STANDING_SIGN)));
		$this->map(Blocks::DARK_OAK_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::DARK_OAK_TRAPDOOR)));
		$this->map(Blocks::DARK_OAK_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::DARKOAK_WALL_SIGN)));
		$this->mapLog(Blocks::DARK_OAK_LOG(), Ids::DARK_OAK_LOG, Ids::STRIPPED_DARK_OAK_LOG);
		$this->mapSimple(Blocks::DARK_OAK_FENCE(), Ids::DARK_OAK_FENCE);
		$this->mapStairs(Blocks::DARK_OAK_STAIRS(), Ids::DARK_OAK_STAIRS);
		//wood, planks and slabs still use the old way of storing wood type

		$this->map(Blocks::JUNGLE_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::JUNGLE_BUTTON)));
		$this->map(Blocks::JUNGLE_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::JUNGLE_DOOR)));
		$this->map(Blocks::JUNGLE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::JUNGLE_FENCE_GATE)));
		$this->map(Blocks::JUNGLE_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::JUNGLE_PRESSURE_PLATE)));
		$this->map(Blocks::JUNGLE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::JUNGLE_STANDING_SIGN)));
		$this->map(Blocks::JUNGLE_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::JUNGLE_TRAPDOOR)));
		$this->map(Blocks::JUNGLE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::JUNGLE_WALL_SIGN)));
		$this->mapLog(Blocks::JUNGLE_LOG(), Ids::JUNGLE_LOG, Ids::STRIPPED_JUNGLE_LOG);
		$this->mapSimple(Blocks::JUNGLE_FENCE(), Ids::JUNGLE_FENCE);
		$this->mapStairs(Blocks::JUNGLE_STAIRS(), Ids::JUNGLE_STAIRS);
		//wood, planks and slabs still use the old way of storing wood type

		$this->map(Blocks::MANGROVE_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::MANGROVE_BUTTON)));
		$this->map(Blocks::MANGROVE_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::MANGROVE_DOOR)));
		$this->map(Blocks::MANGROVE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::MANGROVE_FENCE_GATE)));
		$this->map(Blocks::MANGROVE_LOG(), fn(Wood $block) => Helper::encodeLog($block, Ids::MANGROVE_LOG, Ids::STRIPPED_MANGROVE_LOG));
		$this->map(Blocks::MANGROVE_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::MANGROVE_PRESSURE_PLATE)));
		$this->map(Blocks::MANGROVE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::MANGROVE_STANDING_SIGN)));
		$this->map(Blocks::MANGROVE_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::MANGROVE_TRAPDOOR)));
		$this->map(Blocks::MANGROVE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::MANGROVE_WALL_SIGN)));
		$this->mapSimple(Blocks::MANGROVE_FENCE(), Ids::MANGROVE_FENCE);
		$this->mapSimple(Blocks::MANGROVE_PLANKS(), Ids::MANGROVE_PLANKS);
		$this->mapSlab(Blocks::MANGROVE_SLAB(), Ids::MANGROVE_SLAB, Ids::MANGROVE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::MANGROVE_STAIRS(), Ids::MANGROVE_STAIRS);
		$this->map(Blocks::MANGROVE_WOOD(), function(Wood $block) : Writer{
			//we can't use the standard method for this because mangrove_wood has a useless property attached to it
			if(!$block->isStripped()){
				return Writer::create(Ids::MANGROVE_WOOD)
					->writePillarAxis($block->getAxis())
					->writeBool(StateNames::STRIPPED_BIT, false); //this is useless, but it has to be written
			}else{
				return Writer::create(Ids::STRIPPED_MANGROVE_WOOD)
					->writePillarAxis($block->getAxis());
			}
		});

		$this->map(Blocks::OAK_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::WOODEN_BUTTON)));
		$this->map(Blocks::OAK_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::WOODEN_DOOR)));
		$this->map(Blocks::OAK_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::FENCE_GATE)));
		$this->map(Blocks::OAK_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::WOODEN_PRESSURE_PLATE)));
		$this->map(Blocks::OAK_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::STANDING_SIGN)));
		$this->map(Blocks::OAK_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::TRAPDOOR)));
		$this->map(Blocks::OAK_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::WALL_SIGN)));
		$this->mapLog(Blocks::OAK_LOG(), Ids::OAK_LOG, Ids::STRIPPED_OAK_LOG);
		$this->mapSimple(Blocks::OAK_FENCE(), Ids::OAK_FENCE);
		$this->mapStairs(Blocks::OAK_STAIRS(), Ids::OAK_STAIRS);
		//wood, planks and slabs still use the old way of storing wood type

		$this->mapSimple(Blocks::SPRUCE_FENCE(), Ids::SPRUCE_FENCE);
		$this->mapLog(Blocks::SPRUCE_LOG(), Ids::SPRUCE_LOG, Ids::STRIPPED_SPRUCE_LOG);
		$this->map(Blocks::SPRUCE_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::SPRUCE_BUTTON)));
		$this->map(Blocks::SPRUCE_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::SPRUCE_DOOR)));
		$this->map(Blocks::SPRUCE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::SPRUCE_FENCE_GATE)));
		$this->map(Blocks::SPRUCE_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::SPRUCE_PRESSURE_PLATE)));
		$this->map(Blocks::SPRUCE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::SPRUCE_STANDING_SIGN)));
		$this->mapStairs(Blocks::SPRUCE_STAIRS(), Ids::SPRUCE_STAIRS);
		$this->map(Blocks::SPRUCE_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::SPRUCE_TRAPDOOR)));
		$this->map(Blocks::SPRUCE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::SPRUCE_WALL_SIGN)));
		//wood, planks and slabs still use the old way of storing wood type

		$this->map(Blocks::WARPED_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::WARPED_BUTTON)));
		$this->map(Blocks::WARPED_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::WARPED_DOOR)));
		$this->map(Blocks::WARPED_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::WARPED_FENCE_GATE)));
		$this->map(Blocks::WARPED_HYPHAE(), fn(Wood $block) => Helper::encodeLog($block, Ids::WARPED_HYPHAE, Ids::STRIPPED_WARPED_HYPHAE));
		$this->map(Blocks::WARPED_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::WARPED_PRESSURE_PLATE)));
		$this->map(Blocks::WARPED_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::WARPED_STANDING_SIGN)));
		$this->map(Blocks::WARPED_STEM(), fn(Wood $block) => Helper::encodeLog($block, Ids::WARPED_STEM, Ids::STRIPPED_WARPED_STEM));
		$this->map(Blocks::WARPED_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::WARPED_TRAPDOOR)));
		$this->map(Blocks::WARPED_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::WARPED_WALL_SIGN)));
		$this->mapSimple(Blocks::WARPED_FENCE(), Ids::WARPED_FENCE);
		$this->mapSimple(Blocks::WARPED_PLANKS(), Ids::WARPED_PLANKS);
		$this->mapSlab(Blocks::WARPED_SLAB(), Ids::WARPED_SLAB, Ids::WARPED_DOUBLE_SLAB);
		$this->mapStairs(Blocks::WARPED_STAIRS(), Ids::WARPED_STAIRS);
	}

	private function registerLegacyWoodBlockSerializers() : void{
		foreach([
			StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_PLANKS(),
			StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_PLANKS(),
			StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_PLANKS(),
			StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_PLANKS(),
			StringValues::WOOD_TYPE_OAK => Blocks::OAK_PLANKS(),
			StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_PLANKS(),
		] as $woodType => $block){
			$this->map($block, fn() => Writer::create(Ids::PLANKS)
				->writeString(StateNames::WOOD_TYPE, $woodType));
		}

		foreach([
			StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_SLAB(),
			StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_SLAB(),
			StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_SLAB(),
			StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_SLAB(),
			StringValues::WOOD_TYPE_OAK => Blocks::OAK_SLAB(),
			StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_SLAB(),
		] as $woodType => $block){
			$this->map($block, fn(Slab $block) => Helper::encodeWoodenSlab($block, $woodType));
		}

		foreach([
			Blocks::ACACIA_WOOD(),
			Blocks::BIRCH_WOOD(),
			Blocks::DARK_OAK_WOOD(),
			Blocks::JUNGLE_WOOD(),
			Blocks::OAK_WOOD(),
			Blocks::SPRUCE_WOOD(),
		] as $block){
			$this->map($block, fn(Wood $block) => Helper::encodeAllSidedLog($block));
		}
	}

	private function registerLeavesSerializers() : void{
		//flattened IDs
		$this->map(Blocks::AZALEA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::AZALEA_LEAVES)));
		$this->map(Blocks::CHERRY_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::CHERRY_LEAVES)));
		$this->map(Blocks::FLOWERING_AZALEA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::AZALEA_LEAVES_FLOWERED)));
		$this->map(Blocks::MANGROVE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::MANGROVE_LEAVES)));

		//legacy mess
		$this->map(Blocks::ACACIA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves2($block, StringValues::NEW_LEAF_TYPE_ACACIA));
		$this->map(Blocks::BIRCH_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_BIRCH));
		$this->map(Blocks::DARK_OAK_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves2($block, StringValues::NEW_LEAF_TYPE_DARK_OAK));
		$this->map(Blocks::JUNGLE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_JUNGLE));
		$this->map(Blocks::OAK_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_OAK));
		$this->map(Blocks::SPRUCE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_SPRUCE));
	}

	private function registerSimpleSerializers() : void{
		$this->mapSimple(Blocks::AIR(), Ids::AIR);
		$this->mapSimple(Blocks::AMETHYST(), Ids::AMETHYST_BLOCK);
		$this->mapSimple(Blocks::ANCIENT_DEBRIS(), Ids::ANCIENT_DEBRIS);
		$this->mapSimple(Blocks::BARRIER(), Ids::BARRIER);
		$this->mapSimple(Blocks::BEACON(), Ids::BEACON);
		$this->mapSimple(Blocks::BLACKSTONE(), Ids::BLACKSTONE);
		$this->mapSimple(Blocks::BLUE_ICE(), Ids::BLUE_ICE);
		$this->mapSimple(Blocks::BOOKSHELF(), Ids::BOOKSHELF);
		$this->mapSimple(Blocks::BRICKS(), Ids::BRICK_BLOCK);
		$this->mapSimple(Blocks::BROWN_MUSHROOM(), Ids::BROWN_MUSHROOM);
		$this->mapSimple(Blocks::CALCITE(), Ids::CALCITE);
		$this->mapSimple(Blocks::CARTOGRAPHY_TABLE(), Ids::CARTOGRAPHY_TABLE);
		$this->mapSimple(Blocks::CHISELED_DEEPSLATE(), Ids::CHISELED_DEEPSLATE);
		$this->mapSimple(Blocks::CHISELED_NETHER_BRICKS(), Ids::CHISELED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CHISELED_POLISHED_BLACKSTONE(), Ids::CHISELED_POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::CHORUS_PLANT(), Ids::CHORUS_PLANT);
		$this->mapSimple(Blocks::CLAY(), Ids::CLAY);
		$this->mapSimple(Blocks::COAL(), Ids::COAL_BLOCK);
		$this->mapSimple(Blocks::COAL_ORE(), Ids::COAL_ORE);
		$this->mapSimple(Blocks::COBBLED_DEEPSLATE(), Ids::COBBLED_DEEPSLATE);
		$this->mapSimple(Blocks::COBBLESTONE(), Ids::COBBLESTONE);
		$this->mapSimple(Blocks::COBWEB(), Ids::WEB);
		$this->mapSimple(Blocks::COPPER_ORE(), Ids::COPPER_ORE);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_BRICKS(), Ids::CRACKED_DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_TILES(), Ids::CRACKED_DEEPSLATE_TILES);
		$this->mapSimple(Blocks::CRACKED_NETHER_BRICKS(), Ids::CRACKED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CRACKED_POLISHED_BLACKSTONE_BRICKS(), Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::CRAFTING_TABLE(), Ids::CRAFTING_TABLE);
		$this->mapSimple(Blocks::CRYING_OBSIDIAN(), Ids::CRYING_OBSIDIAN);
		$this->mapSimple(Blocks::DANDELION(), Ids::YELLOW_FLOWER);
		$this->mapSimple(Blocks::DEAD_BUSH(), Ids::DEADBUSH);
		$this->mapSimple(Blocks::DEEPSLATE_BRICKS(), Ids::DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::DEEPSLATE_COAL_ORE(), Ids::DEEPSLATE_COAL_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_COPPER_ORE(), Ids::DEEPSLATE_COPPER_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_DIAMOND_ORE(), Ids::DEEPSLATE_DIAMOND_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_EMERALD_ORE(), Ids::DEEPSLATE_EMERALD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_GOLD_ORE(), Ids::DEEPSLATE_GOLD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_IRON_ORE(), Ids::DEEPSLATE_IRON_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_LAPIS_LAZULI_ORE(), Ids::DEEPSLATE_LAPIS_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_TILES(), Ids::DEEPSLATE_TILES);
		$this->mapSimple(Blocks::DIAMOND(), Ids::DIAMOND_BLOCK);
		$this->mapSimple(Blocks::DIAMOND_ORE(), Ids::DIAMOND_ORE);
		$this->mapSimple(Blocks::DRAGON_EGG(), Ids::DRAGON_EGG);
		$this->mapSimple(Blocks::DRIED_KELP(), Ids::DRIED_KELP_BLOCK);
		$this->mapSimple(Blocks::EMERALD(), Ids::EMERALD_BLOCK);
		$this->mapSimple(Blocks::EMERALD_ORE(), Ids::EMERALD_ORE);
		$this->mapSimple(Blocks::ENCHANTING_TABLE(), Ids::ENCHANTING_TABLE);
		$this->mapSimple(Blocks::END_STONE(), Ids::END_STONE);
		$this->mapSimple(Blocks::END_STONE_BRICKS(), Ids::END_BRICKS);
		$this->mapSimple(Blocks::FLETCHING_TABLE(), Ids::FLETCHING_TABLE);
		$this->mapSimple(Blocks::GILDED_BLACKSTONE(), Ids::GILDED_BLACKSTONE);
		$this->mapSimple(Blocks::GLASS(), Ids::GLASS);
		$this->mapSimple(Blocks::GLASS_PANE(), Ids::GLASS_PANE);
		$this->mapSimple(Blocks::GLOWING_OBSIDIAN(), Ids::GLOWINGOBSIDIAN);
		$this->mapSimple(Blocks::GLOWSTONE(), Ids::GLOWSTONE);
		$this->mapSimple(Blocks::GOLD(), Ids::GOLD_BLOCK);
		$this->mapSimple(Blocks::GOLD_ORE(), Ids::GOLD_ORE);
		$this->mapSimple(Blocks::GRASS(), Ids::GRASS);
		$this->mapSimple(Blocks::GRASS_PATH(), Ids::GRASS_PATH);
		$this->mapSimple(Blocks::GRAVEL(), Ids::GRAVEL);
		$this->mapSimple(Blocks::HANGING_ROOTS(), Ids::HANGING_ROOTS);
		$this->mapSimple(Blocks::HARDENED_CLAY(), Ids::HARDENED_CLAY);
		$this->mapSimple(Blocks::HARDENED_GLASS(), Ids::HARD_GLASS);
		$this->mapSimple(Blocks::HARDENED_GLASS_PANE(), Ids::HARD_GLASS_PANE);
		$this->mapSimple(Blocks::HONEYCOMB(), Ids::HONEYCOMB_BLOCK);
		$this->mapSimple(Blocks::ICE(), Ids::ICE);
		$this->mapSimple(Blocks::INFO_UPDATE(), Ids::INFO_UPDATE);
		$this->mapSimple(Blocks::INFO_UPDATE2(), Ids::INFO_UPDATE2);
		$this->mapSimple(Blocks::INVISIBLE_BEDROCK(), Ids::INVISIBLE_BEDROCK);
		$this->mapSimple(Blocks::IRON(), Ids::IRON_BLOCK);
		$this->mapSimple(Blocks::IRON_BARS(), Ids::IRON_BARS);
		$this->mapSimple(Blocks::IRON_ORE(), Ids::IRON_ORE);
		$this->mapSimple(Blocks::JUKEBOX(), Ids::JUKEBOX);
		$this->mapSimple(Blocks::LAPIS_LAZULI(), Ids::LAPIS_BLOCK);
		$this->mapSimple(Blocks::LAPIS_LAZULI_ORE(), Ids::LAPIS_ORE);
		$this->mapSimple(Blocks::LEGACY_STONECUTTER(), Ids::STONECUTTER);
		$this->mapSimple(Blocks::LILY_PAD(), Ids::WATERLILY);
		$this->mapSimple(Blocks::MAGMA(), Ids::MAGMA);
		$this->mapSimple(Blocks::MANGROVE_ROOTS(), Ids::MANGROVE_ROOTS);
		$this->mapSimple(Blocks::MELON(), Ids::MELON_BLOCK);
		$this->mapSimple(Blocks::MONSTER_SPAWNER(), Ids::MOB_SPAWNER);
		$this->mapSimple(Blocks::MOSSY_COBBLESTONE(), Ids::MOSSY_COBBLESTONE);
		$this->mapSimple(Blocks::MUD(), Ids::MUD);
		$this->mapSimple(Blocks::MUD_BRICKS(), Ids::MUD_BRICKS);
		$this->mapSimple(Blocks::MYCELIUM(), Ids::MYCELIUM);
		$this->mapSimple(Blocks::NETHERITE(), Ids::NETHERITE_BLOCK);
		$this->mapSimple(Blocks::NETHERRACK(), Ids::NETHERRACK);
		$this->mapSimple(Blocks::NETHER_BRICKS(), Ids::NETHER_BRICK);
		$this->mapSimple(Blocks::NETHER_BRICK_FENCE(), Ids::NETHER_BRICK_FENCE);
		$this->mapSimple(Blocks::NETHER_GOLD_ORE(), Ids::NETHER_GOLD_ORE);
		$this->mapSimple(Blocks::NETHER_QUARTZ_ORE(), Ids::QUARTZ_ORE);
		$this->mapSimple(Blocks::NETHER_REACTOR_CORE(), Ids::NETHERREACTOR);
		$this->mapSimple(Blocks::NETHER_WART_BLOCK(), Ids::NETHER_WART_BLOCK);
		$this->mapSimple(Blocks::NOTE_BLOCK(), Ids::NOTEBLOCK);
		$this->mapSimple(Blocks::OBSIDIAN(), Ids::OBSIDIAN);
		$this->mapSimple(Blocks::PACKED_ICE(), Ids::PACKED_ICE);
		$this->mapSimple(Blocks::PACKED_MUD(), Ids::PACKED_MUD);
		$this->mapSimple(Blocks::PODZOL(), Ids::PODZOL);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE(), Ids::POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE_BRICKS(), Ids::POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::POLISHED_DEEPSLATE(), Ids::POLISHED_DEEPSLATE);
		$this->mapSimple(Blocks::QUARTZ_BRICKS(), Ids::QUARTZ_BRICKS);
		$this->mapSimple(Blocks::RAW_COPPER(), Ids::RAW_COPPER_BLOCK);
		$this->mapSimple(Blocks::RAW_GOLD(), Ids::RAW_GOLD_BLOCK);
		$this->mapSimple(Blocks::RAW_IRON(), Ids::RAW_IRON_BLOCK);
		$this->mapSimple(Blocks::REDSTONE(), Ids::REDSTONE_BLOCK);
		$this->mapSimple(Blocks::RED_MUSHROOM(), Ids::RED_MUSHROOM);
		$this->mapSimple(Blocks::RED_NETHER_BRICKS(), Ids::RED_NETHER_BRICK);
		$this->mapSimple(Blocks::REINFORCED_DEEPSLATE(), Ids::REINFORCED_DEEPSLATE);
		$this->mapSimple(Blocks::RESERVED6(), Ids::RESERVED6);
		$this->mapSimple(Blocks::SCULK(), Ids::SCULK);
		$this->mapSimple(Blocks::SEA_LANTERN(), Ids::SEA_LANTERN);
		$this->mapSimple(Blocks::SHROOMLIGHT(), Ids::SHROOMLIGHT);
		$this->mapSimple(Blocks::SHULKER_BOX(), Ids::UNDYED_SHULKER_BOX);
		$this->mapSimple(Blocks::SLIME(), Ids::SLIME);
		$this->mapSimple(Blocks::SMITHING_TABLE(), Ids::SMITHING_TABLE);
		$this->mapSimple(Blocks::SMOOTH_BASALT(), Ids::SMOOTH_BASALT);
		$this->mapSimple(Blocks::SMOOTH_STONE(), Ids::SMOOTH_STONE);
		$this->mapSimple(Blocks::SNOW(), Ids::SNOW);
		$this->mapSimple(Blocks::SOUL_SAND(), Ids::SOUL_SAND);
		$this->mapSimple(Blocks::SOUL_SOIL(), Ids::SOUL_SOIL);
		$this->mapSimple(Blocks::SPORE_BLOSSOM(), Ids::SPORE_BLOSSOM);
		$this->mapSimple(Blocks::TINTED_GLASS(), Ids::TINTED_GLASS);
		$this->mapSimple(Blocks::TUFF(), Ids::TUFF);
		$this->mapSimple(Blocks::WARPED_WART_BLOCK(), Ids::WARPED_WART_BLOCK);
		$this->mapSimple(Blocks::WITHER_ROSE(), Ids::WITHER_ROSE);
	}

	private function registerSerializers() : void{
		$this->map(Blocks::ACACIA_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_ACACIA));
		$this->map(Blocks::ACTIVATOR_RAIL(), function(ActivatorRail $block) : Writer{
			return Writer::create(Ids::ACTIVATOR_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::ALLIUM(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_ALLIUM));
		$this->map(Blocks::ALL_SIDED_MUSHROOM_STEM(), fn() => Writer::create(Ids::BROWN_MUSHROOM_BLOCK)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM));
		$this->map(Blocks::ANDESITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_ANDESITE));
		$this->map(Blocks::ANDESITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_ANDESITE));
		$this->map(Blocks::ANDESITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::ANDESITE_STAIRS)));
		$this->map(Blocks::ANDESITE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_ANDESITE));
		$this->map(Blocks::ANVIL(), function(Anvil $block) : Writer{
			return Writer::create(Ids::ANVIL)
				->writeLegacyHorizontalFacing($block->getFacing())
				->writeString(StateNames::DAMAGE, match($damage = $block->getDamage()){
					0 => StringValues::DAMAGE_UNDAMAGED,
					1 => StringValues::DAMAGE_SLIGHTLY_DAMAGED,
					2 => StringValues::DAMAGE_VERY_DAMAGED,
					default => throw new BlockStateSerializeException("Invalid Anvil damage {$damage}"),
				});
		});
		$this->map(Blocks::AZURE_BLUET(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_HOUSTONIA));
		$this->map(Blocks::BAMBOO(), function(Bamboo $block) : Writer{
			return Writer::create(Ids::BAMBOO)
				->writeBool(StateNames::AGE_BIT, $block->isReady())
				->writeString(StateNames::BAMBOO_LEAF_SIZE, match($block->getLeafSize()){
					Bamboo::NO_LEAVES => StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES,
					Bamboo::SMALL_LEAVES => StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES,
					Bamboo::LARGE_LEAVES => StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES,
					default => throw new BlockStateSerializeException("Invalid Bamboo leaf thickness " . $block->getLeafSize()),
				})
				->writeString(StateNames::BAMBOO_STALK_THICKNESS, $block->isThick() ? StringValues::BAMBOO_STALK_THICKNESS_THICK : StringValues::BAMBOO_STALK_THICKNESS_THIN);
		});
		$this->map(Blocks::SCAFFOLDING(), function(\wolfcore\block\Scaffolding $block) : Writer{
			return Writer::create(Ids::SCAFFOLDING)
				->writeInt(StateNames::STABILITY, $block->getStability())
				->writeBool(StateNames::STABILITY_CHECK, $block->hasStabilityCheck());
		});
		$this->map(Blocks::BAMBOO_SAPLING(), function(BambooSapling $block) : Writer{
			return Writer::create(Ids::BAMBOO_SAPLING)
				->writeBool(StateNames::AGE_BIT, $block->isReady())

				//TODO: bug in MCPE
				->writeString(StateNames::SAPLING_TYPE, StringValues::SAPLING_TYPE_OAK);
		});
		$this->map(Blocks::BANNER(), function(FloorBanner $block) : Writer{
			return Writer::create(Ids::STANDING_BANNER)
				->writeInt(StateNames::GROUND_SIGN_DIRECTION, $block->getRotation());
		});
		$this->map(Blocks::BARREL(), function(Barrel $block) : Writer{
			return Writer::create(Ids::BARREL)
				->writeBool(StateNames::OPEN_BIT, $block->isOpen())
				->writeFacingDirection($block->getFacing());
		});
		$this->map(Blocks::BASALT(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::BASALT)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::BED(), function(Bed $block) : Writer{
			return Writer::create(Ids::BED)
				->writeBool(StateNames::HEAD_PIECE_BIT, $block->isHeadPart())
				->writeBool(StateNames::OCCUPIED_BIT, $block->isOccupied())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::BEDROCK(), function(Block $block) : Writer{
			return Writer::create(Ids::BEDROCK)
				->writeBool(StateNames::INFINIBURN_BIT, $block->burnsForever());
		});
		$this->map(Blocks::BEETROOTS(), fn(Beetroot $block) => Helper::encodeCrops($block, new Writer(Ids::BEETROOT)));
		$this->map(Blocks::BELL(), function(Bell $block) : Writer{
			return Writer::create(Ids::BELL)
				->writeBellAttachmentType($block->getAttachmentType())
				->writeBool(StateNames::TOGGLE_BIT, false) //we don't care about this; it's just to keep MCPE happy
				->writeLegacyHorizontalFacing($block->getFacing());

		});
		$this->map(Blocks::BIRCH_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_BIRCH));
		$this->mapSlab(Blocks::BLACKSTONE_SLAB(), Ids::BLACKSTONE_SLAB, Ids::BLACKSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::BLACKSTONE_STAIRS(), Ids::BLACKSTONE_STAIRS);
		$this->map(Blocks::BLACKSTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::BLACKSTONE_WALL)));
		$this->map(Blocks::BLAST_FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::BLAST_FURNACE, Ids::LIT_BLAST_FURNACE));
		$this->map(Blocks::BLUE_ORCHID(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_ORCHID));
		$this->map(Blocks::BLUE_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, false, Writer::create(Ids::COLORED_TORCH_BP)));
		$this->map(Blocks::BONE_BLOCK(), function(BoneBlock $block) : Writer{
			return Writer::create(Ids::BONE_BLOCK)
				->writeInt(StateNames::DEPRECATED, 0)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::BREWING_STAND(), function(BrewingStand $block) : Writer{
			return Writer::create(Ids::BREWING_STAND)
				->writeBool(StateNames::BREWING_STAND_SLOT_A_BIT, $block->hasSlot(BrewingStandSlot::EAST()))
				->writeBool(StateNames::BREWING_STAND_SLOT_B_BIT, $block->hasSlot(BrewingStandSlot::SOUTHWEST()))
				->writeBool(StateNames::BREWING_STAND_SLOT_C_BIT, $block->hasSlot(BrewingStandSlot::NORTHWEST()));
		});
		$this->map(Blocks::BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_BRICK));
		$this->mapStairs(Blocks::BRICK_STAIRS(), Ids::BRICK_STAIRS);
		$this->map(Blocks::BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_BRICK));
		$this->map(Blocks::BROWN_MUSHROOM_BLOCK(), fn(BrownMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::BROWN_MUSHROOM_BLOCK)));
		$this->map(Blocks::CACTUS(), function(Cactus $block) : Writer{
			return Writer::create(Ids::CACTUS)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::CAKE(), function(Cake $block) : Writer{
			return Writer::create(Ids::CAKE)
				->writeInt(StateNames::BITE_COUNTER, $block->getBites());
		});
		$this->map(Blocks::CARROTS(), fn(Carrot $block) => Helper::encodeCrops($block, new Writer(Ids::CARROTS)));
		$this->map(Blocks::CARVED_PUMPKIN(), function(CarvedPumpkin $block) : Writer{
			return Writer::create(Ids::CARVED_PUMPKIN)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::CAVE_VINES(), function(CaveVines $block) : Writer{
			//I have no idea why this only has 3 IDs - there are 4 in Java and 4 visually distinct states in Bedrock
			return Writer::create($block->hasBerries() ?
				($block->isHead() ?
					Ids::CAVE_VINES_HEAD_WITH_BERRIES :
					Ids::CAVE_VINES_BODY_WITH_BERRIES
				) :
				Ids::CAVE_VINES
			)
				->writeInt(StateNames::GROWING_PLANT_AGE, $block->getAge());
		});
		$this->map(Blocks::CHAIN(), function(Chain $block) : Writer{
			return Writer::create(Ids::CHAIN)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::CHEST(), function(Chest $block) : Writer{
			return Writer::create(Ids::CHEST)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::CHISELED_QUARTZ(), fn(SimplePillar $block) => Helper::encodeQuartz(StringValues::CHISEL_TYPE_CHISELED, $block->getAxis()));
		$this->map(Blocks::CHISELED_RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_HEIROGLYPHS));
		$this->map(Blocks::CHISELED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_HEIROGLYPHS));
		$this->map(Blocks::CHISELED_STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_CHISELED));
		$this->map(Blocks::CHORUS_FLOWER(), function(ChorusFlower $block) : Writer{
			return Writer::create(Ids::CHORUS_FLOWER)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->mapSlab(Blocks::COBBLED_DEEPSLATE_SLAB(), Ids::COBBLED_DEEPSLATE_SLAB, Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::COBBLED_DEEPSLATE_STAIRS(), Ids::COBBLED_DEEPSLATE_STAIRS);
		$this->map(Blocks::COBBLED_DEEPSLATE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::COBBLED_DEEPSLATE_WALL)));
		$this->map(Blocks::COBBLESTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_COBBLESTONE));
		$this->mapStairs(Blocks::COBBLESTONE_STAIRS(), Ids::STONE_STAIRS);
		$this->map(Blocks::COBBLESTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_COBBLESTONE));
		$this->map(Blocks::COPPER(), function(Copper $block) : Writer{
			$oxidation = $block->getOxidation();
			return new Writer($block->isWaxed() ?
				Helper::selectCopperId($oxidation, Ids::WAXED_COPPER, Ids::WAXED_EXPOSED_COPPER, Ids::WAXED_WEATHERED_COPPER, Ids::WAXED_OXIDIZED_COPPER) :
				Helper::selectCopperId($oxidation, Ids::COPPER_BLOCK, Ids::EXPOSED_COPPER, Ids::WEATHERED_COPPER, Ids::OXIDIZED_COPPER)
			);
		});
		$this->map(Blocks::CUT_COPPER(), function(Copper $block) : Writer{
			$oxidation = $block->getOxidation();
			return new Writer($block->isWaxed() ?
				Helper::selectCopperId($oxidation, Ids::WAXED_CUT_COPPER, Ids::WAXED_EXPOSED_CUT_COPPER, Ids::WAXED_WEATHERED_CUT_COPPER, Ids::WAXED_OXIDIZED_CUT_COPPER) :
				Helper::selectCopperId($oxidation, Ids::CUT_COPPER, Ids::EXPOSED_CUT_COPPER, Ids::WEATHERED_CUT_COPPER, Ids::OXIDIZED_CUT_COPPER)
			);
		});
		$this->map(Blocks::CUT_COPPER_SLAB(), function(CopperSlab $block) : Writer{
			$oxidation = $block->getOxidation();
			return Helper::encodeSlab(
				$block,
				($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_CUT_COPPER_SLAB,
						Ids::WAXED_EXPOSED_CUT_COPPER_SLAB,
						Ids::WAXED_WEATHERED_CUT_COPPER_SLAB,
						Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::CUT_COPPER_SLAB,
						Ids::EXPOSED_CUT_COPPER_SLAB,
						Ids::WEATHERED_CUT_COPPER_SLAB,
						Ids::OXIDIZED_CUT_COPPER_SLAB
					)
				),
				($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::DOUBLE_CUT_COPPER_SLAB,
						Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB,
						Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB
					)
				)
			);
		});
		$this->map(Blocks::CUT_COPPER_STAIRS(), function(CopperStairs $block) : Writer{
			$oxidation = $block->getOxidation();
			return Helper::encodeStairs(
				$block,
				new Writer($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_CUT_COPPER_STAIRS,
						Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS,
						Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS,
						Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::CUT_COPPER_STAIRS,
						Ids::EXPOSED_CUT_COPPER_STAIRS,
						Ids::WEATHERED_CUT_COPPER_STAIRS,
						Ids::OXIDIZED_CUT_COPPER_STAIRS
					)
				)
			);
		});
		$this->map(Blocks::COCOA_POD(), function(CocoaBlock $block) : Writer{
			return Writer::create(Ids::COCOA)
				->writeInt(StateNames::AGE, $block->getAge())
				->writeLegacyHorizontalFacing(Facing::opposite($block->getFacing()));
		});
		$this->map(Blocks::CONCRETE(), function(Concrete $block) : Writer{
			return Writer::create(Ids::CONCRETE)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::CONCRETE_POWDER(), function(ConcretePowder $block) : Writer{
			return Writer::create(Ids::CONCRETE_POWDER)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::CORAL_BLOCK(), function(CoralBlock $block) : Writer{
			return Writer::create(Ids::CORAL_BLOCK)
				->writeBool(StateNames::DEAD_BIT, $block->isDead())
				->writeCoralType($block->getCoralType());
		});
		$this->map(Blocks::CORAL_FAN(), function(FloorCoralFan $block) : Writer{
			return Writer::create($block->isDead() ? Ids::CORAL_FAN_DEAD : Ids::CORAL_FAN)
				->writeCoralType($block->getCoralType())
				->writeInt(StateNames::CORAL_FAN_DIRECTION, match($axis = $block->getAxis()){
					Axis::X => 0,
					Axis::Z => 1,
					default => throw new BlockStateSerializeException("Invalid axis {$axis}"),
				});
		});
		$this->map(Blocks::CORNFLOWER(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_CORNFLOWER));
		$this->map(Blocks::CRACKED_STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_CRACKED));
		$this->map(Blocks::CUT_RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_CUT));
		$this->map(Blocks::CUT_RED_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_CUT_RED_SANDSTONE));
		$this->map(Blocks::CUT_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_CUT));
		$this->map(Blocks::CUT_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_CUT_SANDSTONE));
		$this->map(Blocks::DARK_OAK_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_DARK_OAK));
		$this->map(Blocks::DARK_PRISMARINE(), fn() => Writer::create(Ids::PRISMARINE)
				->writeString(StateNames::PRISMARINE_BLOCK_TYPE, StringValues::PRISMARINE_BLOCK_TYPE_DARK));
		$this->map(Blocks::DARK_PRISMARINE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PRISMARINE_DARK));
		$this->mapStairs(Blocks::DARK_PRISMARINE_STAIRS(), Ids::DARK_PRISMARINE_STAIRS);
		$this->map(Blocks::DAYLIGHT_SENSOR(), function(DaylightSensor $block) : Writer{
			return Writer::create($block->isInverted() ? Ids::DAYLIGHT_DETECTOR_INVERTED : Ids::DAYLIGHT_DETECTOR)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::DEEPSLATE(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::DEEPSLATE)
				->writePillarAxis($block->getAxis());
		});
		$this->mapSlab(Blocks::DEEPSLATE_BRICK_SLAB(), Ids::DEEPSLATE_BRICK_SLAB, Ids::DEEPSLATE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DEEPSLATE_BRICK_STAIRS(), Ids::DEEPSLATE_BRICK_STAIRS);
		$this->map(Blocks::DEEPSLATE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::DEEPSLATE_BRICK_WALL)));
		$this->map(Blocks::DEEPSLATE_REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_DEEPSLATE_REDSTONE_ORE : Ids::DEEPSLATE_REDSTONE_ORE));
		$this->mapSlab(Blocks::DEEPSLATE_TILE_SLAB(), Ids::DEEPSLATE_TILE_SLAB, Ids::DEEPSLATE_TILE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DEEPSLATE_TILE_STAIRS(), Ids::DEEPSLATE_TILE_STAIRS);
		$this->map(Blocks::DEEPSLATE_TILE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::DEEPSLATE_TILE_WALL)));
		$this->map(Blocks::DETECTOR_RAIL(), function(DetectorRail $block) : Writer{
			return Writer::create(Ids::DETECTOR_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isActivated())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::DIORITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_DIORITE));
		$this->map(Blocks::DIORITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_DIORITE));
		$this->mapStairs(Blocks::DIORITE_STAIRS(), Ids::DIORITE_STAIRS);
		$this->map(Blocks::DIORITE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_DIORITE));
		$this->map(Blocks::DIRT(), function(Dirt $block) : Writer{
			$dirtType = $block->getDirtType();
			if($dirtType->equals(DirtType::ROOTED())){
				return new Writer(Ids::DIRT_WITH_ROOTS);
			}
			return Writer::create(Ids::DIRT)
				->writeString(StateNames::DIRT_TYPE, match($dirtType){
					DirtType::COARSE() => StringValues::DIRT_TYPE_COARSE,
					DirtType::NORMAL() => StringValues::DIRT_TYPE_NORMAL,
					default => throw new AssumptionFailedError("Unhandled dirt type " . $dirtType->name())
				});
		});
		$this->map(Blocks::DOUBLE_TALLGRASS(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_GRASS, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(Blocks::DYED_SHULKER_BOX(), function(DyedShulkerBox $block) : Writer{
			return Writer::create(Ids::SHULKER_BOX)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::ENDER_CHEST(), function(EnderChest $block) : Writer{
			return Writer::create(Ids::ENDER_CHEST)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::END_PORTAL_FRAME(), function(EndPortalFrame $block) : Writer{
			return Writer::create(Ids::END_PORTAL_FRAME)
				->writeBool(StateNames::END_PORTAL_EYE_BIT, $block->hasEye())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::END_ROD(), function(EndRod $block) : Writer{
			return Writer::create(Ids::END_ROD)
				->writeEndRodFacingDirection($block->getFacing());
		});
		$this->map(Blocks::END_STONE_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_END_STONE_BRICK));
		$this->mapStairs(Blocks::END_STONE_BRICK_STAIRS(), Ids::END_BRICK_STAIRS);
		$this->map(Blocks::END_STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_END_BRICK));
		$this->map(Blocks::FAKE_WOODEN_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_WOOD));
		$this->map(Blocks::FARMLAND(), function(Farmland $block) : Writer{
			return Writer::create(Ids::FARMLAND)
				->writeInt(StateNames::MOISTURIZED_AMOUNT, $block->getWetness());
		});
		$this->map(Blocks::FERN(), fn() => Writer::create(Ids::TALLGRASS)
				->writeString(StateNames::TALL_GRASS_TYPE, StringValues::TALL_GRASS_TYPE_FERN));
		$this->map(Blocks::FIRE(), function(Fire $block) : Writer{
			return Writer::create(Ids::FIRE)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::FLOWER_POT(), function() : Writer{
			return Writer::create(Ids::FLOWER_POT)
				->writeBool(StateNames::UPDATE_BIT, false); //to keep MCPE happy
		});
		$this->map(Blocks::FROGLIGHT(), function(Froglight $block){
			return Writer::create(match($block->getFroglightType()){
				FroglightType::OCHRE() => Ids::OCHRE_FROGLIGHT,
				FroglightType::PEARLESCENT() => Ids::PEARLESCENT_FROGLIGHT,
				FroglightType::VERDANT() => Ids::VERDANT_FROGLIGHT,
				default => throw new AssumptionFailedError("Unhandled froglight type " . $block->getFroglightType()->name())
			})
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::FROSTED_ICE(), function(FrostedIce $block) : Writer{
			return Writer::create(Ids::FROSTED_ICE)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::FURNACE, Ids::LIT_FURNACE));
		$this->map(Blocks::GLOW_LICHEN(), function(GlowLichen $block) : Writer{
			return Writer::create(Ids::GLOW_LICHEN)
				->writeFacingFlags($block->getFaces());
		});
		$this->map(Blocks::GLOWING_ITEM_FRAME(), fn(ItemFrame $block) => Helper::encodeItemFrame($block, Ids::GLOW_FRAME));
		$this->map(Blocks::GRANITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_GRANITE));
		$this->map(Blocks::GRANITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_GRANITE));
		$this->mapStairs(Blocks::GRANITE_STAIRS(), Ids::GRANITE_STAIRS);
		$this->map(Blocks::GRANITE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_GRANITE));
		$this->map(Blocks::GREEN_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, true, Writer::create(Ids::COLORED_TORCH_RG)));
		$this->map(Blocks::HAY_BALE(), function(HayBale $block) : Writer{
			return Writer::create(Ids::HAY_BLOCK)
				->writeInt(StateNames::DEPRECATED, 0)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::HOPPER(), function(Hopper $block) : Writer{
			return Writer::create(Ids::HOPPER)
				->writeBool(StateNames::TOGGLE_BIT, $block->isPowered())
				->writeFacingWithoutUp($block->getFacing());
		});
		$this->map(Blocks::INFESTED_CHISELED_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(StateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_CHISELED_STONE_BRICK));
		$this->map(Blocks::INFESTED_COBBLESTONE(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(StateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_COBBLESTONE));
		$this->map(Blocks::INFESTED_CRACKED_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(StateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_CRACKED_STONE_BRICK));
		$this->map(Blocks::INFESTED_MOSSY_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(StateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_MOSSY_STONE_BRICK));
		$this->map(Blocks::INFESTED_STONE(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(StateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_STONE));
		$this->map(Blocks::INFESTED_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(StateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_STONE_BRICK));
		$this->map(Blocks::IRON_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::IRON_DOOR)));
		$this->map(Blocks::IRON_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::IRON_TRAPDOOR)));
		$this->map(Blocks::ITEM_FRAME(), fn(ItemFrame $block) => Helper::encodeItemFrame($block, Ids::FRAME));
		$this->map(Blocks::JUNGLE_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_JUNGLE));
		$this->map(Blocks::LADDER(), function(Ladder $block) : Writer{
			return Writer::create(Ids::LADDER)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::LANTERN(), function(Lantern $block) : Writer{
			return Writer::create(Ids::LANTERN)
				->writeBool(StateNames::HANGING, $block->isHanging());
		});
		$this->map(Blocks::LARGE_FERN(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_FERN, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(Blocks::LAVA(), fn(Lava $block) => Helper::encodeLiquid($block, Ids::LAVA, Ids::FLOWING_LAVA));
		$this->map(Blocks::LECTERN(), function(Lectern $block) : Writer{
			return Writer::create(Ids::LECTERN)
				->writeBool(StateNames::POWERED_BIT, $block->isProducingSignal())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::LEVER(), function(Lever $block) : Writer{
			return Writer::create(Ids::LEVER)
				->writeBool(StateNames::OPEN_BIT, $block->isActivated())
				->writeString(StateNames::LEVER_DIRECTION, match($block->getFacing()->id()){
					LeverFacing::DOWN_AXIS_Z()->id() => StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
					LeverFacing::DOWN_AXIS_X()->id() => StringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
					LeverFacing::UP_AXIS_Z()->id() => StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
					LeverFacing::UP_AXIS_X()->id() => StringValues::LEVER_DIRECTION_UP_EAST_WEST,
					LeverFacing::NORTH()->id() => StringValues::LEVER_DIRECTION_NORTH,
					LeverFacing::SOUTH()->id() => StringValues::LEVER_DIRECTION_SOUTH,
					LeverFacing::WEST()->id() => StringValues::LEVER_DIRECTION_WEST,
					LeverFacing::EAST()->id() => StringValues::LEVER_DIRECTION_EAST,
					default => throw new BlockStateSerializeException("Invalid Lever facing " . $block->getFacing()->name()),
				});
		});
		$this->map(Blocks::LIGHT(), function(Light $block) : Writer{
			return Writer::create(Ids::LIGHT_BLOCK)
				->writeInt(StateNames::BLOCK_LIGHT_LEVEL, $block->getLightLevel());
		});
		$this->map(Blocks::LIGHTNING_ROD(), function(LightningRod $block) : Writer{
			return Writer::create(Ids::LIGHTNING_ROD)
				->writeFacingDirection($block->getFacing());
		});
		$this->map(Blocks::LILAC(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_SYRINGA, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(Blocks::LILY_OF_THE_VALLEY(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_LILY_OF_THE_VALLEY));
		$this->map(Blocks::LIT_PUMPKIN(), function(LitPumpkin $block) : Writer{
			return Writer::create(Ids::LIT_PUMPKIN)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::LOOM(), function(Loom $block) : Writer{
			return Writer::create(Ids::LOOM)
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::MELON_STEM(), fn(MelonStem $block) => Helper::encodeStem($block, new Writer(Ids::MELON_STEM)));
		$this->map(Blocks::MOB_HEAD(), function(MobHead $block) : Writer{
			return Writer::create(Ids::SKULL)
				->writeFacingWithoutDown($block->getFacing());
		});
		$this->map(Blocks::MOSSY_COBBLESTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_MOSSY_COBBLESTONE));
		$this->mapStairs(Blocks::MOSSY_COBBLESTONE_STAIRS(), Ids::MOSSY_COBBLESTONE_STAIRS);
		$this->map(Blocks::MOSSY_COBBLESTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_MOSSY_COBBLESTONE));
		$this->map(Blocks::MOSSY_STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_MOSSY));
		$this->map(Blocks::MOSSY_STONE_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_MOSSY_STONE_BRICK));
		$this->mapStairs(Blocks::MOSSY_STONE_BRICK_STAIRS(), Ids::MOSSY_STONE_BRICK_STAIRS);
		$this->map(Blocks::MOSSY_STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_MOSSY_STONE_BRICK));
		$this->mapSlab(Blocks::MUD_BRICK_SLAB(), Ids::MUD_BRICK_SLAB, Ids::MUD_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::MUD_BRICK_STAIRS(), Ids::MUD_BRICK_STAIRS);
		$this->map(Blocks::MUD_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::MUD_BRICK_WALL)));
		$this->map(Blocks::MUDDY_MANGROVE_ROOTS(), fn(SimplePillar $block) => Writer::create(Ids::MUDDY_MANGROVE_ROOTS)
				->writePillarAxis($block->getAxis()));
		$this->map(Blocks::MUSHROOM_STEM(), fn() => Writer::create(Ids::BROWN_MUSHROOM_BLOCK)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
		$this->map(Blocks::NETHER_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_NETHER_BRICK));
		$this->mapStairs(Blocks::NETHER_BRICK_STAIRS(), Ids::NETHER_BRICK_STAIRS);
		$this->map(Blocks::NETHER_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_NETHER_BRICK));
		$this->map(Blocks::NETHER_PORTAL(), function(NetherPortal $block) : Writer{
			return Writer::create(Ids::PORTAL)
				->writeString(StateNames::PORTAL_AXIS, match($block->getAxis()){
					Axis::X => StringValues::PORTAL_AXIS_X,
					Axis::Z => StringValues::PORTAL_AXIS_Z,
					default => throw new BlockStateSerializeException("Invalid Nether Portal axis " . $block->getAxis()),
				});
		});
		$this->map(Blocks::NETHER_WART(), function(NetherWartPlant $block) : Writer{
			return Writer::create(Ids::NETHER_WART)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::OAK_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_OAK));
		$this->map(Blocks::ORANGE_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_ORANGE));
		$this->map(Blocks::OXEYE_DAISY(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_OXEYE));
		$this->map(Blocks::PEONY(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_PAEONIA, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(Blocks::PINK_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_PINK));
		$this->map(Blocks::POLISHED_ANDESITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_ANDESITE_SMOOTH));
		$this->map(Blocks::POLISHED_ANDESITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_POLISHED_ANDESITE));
		$this->mapStairs(Blocks::POLISHED_ANDESITE_STAIRS(), Ids::POLISHED_ANDESITE_STAIRS);
		$this->map(Blocks::POLISHED_BASALT(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::POLISHED_BASALT)
				->writePillarAxis($block->getAxis());
		});
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_BRICK_SLAB(), Ids::POLISHED_BLACKSTONE_BRICK_SLAB, Ids::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_BRICK_STAIRS(), Ids::POLISHED_BLACKSTONE_BRICK_STAIRS);
		$this->map(Blocks::POLISHED_BLACKSTONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_BLACKSTONE_BRICK_WALL)));
		$this->map(Blocks::POLISHED_BLACKSTONE_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::POLISHED_BLACKSTONE_BUTTON)));
		$this->map(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE)));
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_SLAB(), Ids::POLISHED_BLACKSTONE_SLAB, Ids::POLISHED_BLACKSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_STAIRS(), Ids::POLISHED_BLACKSTONE_STAIRS);
		$this->map(Blocks::POLISHED_BLACKSTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_BLACKSTONE_WALL)));
		$this->mapSlab(Blocks::POLISHED_DEEPSLATE_SLAB(), Ids::POLISHED_DEEPSLATE_SLAB, Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_DEEPSLATE_STAIRS(), Ids::POLISHED_DEEPSLATE_STAIRS);
		$this->map(Blocks::POLISHED_DEEPSLATE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_DEEPSLATE_WALL)));
		$this->map(Blocks::POLISHED_DIORITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_DIORITE_SMOOTH));
		$this->map(Blocks::POLISHED_DIORITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_POLISHED_DIORITE));
		$this->mapStairs(Blocks::POLISHED_DIORITE_STAIRS(), Ids::POLISHED_DIORITE_STAIRS);
		$this->map(Blocks::POLISHED_GRANITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_GRANITE_SMOOTH));
		$this->map(Blocks::POLISHED_GRANITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_POLISHED_GRANITE));
		$this->mapStairs(Blocks::POLISHED_GRANITE_STAIRS(), Ids::POLISHED_GRANITE_STAIRS);
		$this->map(Blocks::POPPY(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_POPPY));
		$this->map(Blocks::POTATOES(), fn(Potato $block) => Helper::encodeCrops($block, new Writer(Ids::POTATOES)));
		$this->map(Blocks::POWERED_RAIL(), function(PoweredRail $block) : Writer{
			return Writer::create(Ids::GOLDEN_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::PRISMARINE(), fn() => Writer::create(Ids::PRISMARINE)
				->writeString(StateNames::PRISMARINE_BLOCK_TYPE, StringValues::PRISMARINE_BLOCK_TYPE_DEFAULT));
		$this->map(Blocks::PRISMARINE_BRICKS(), fn() => Writer::create(Ids::PRISMARINE)
				->writeString(StateNames::PRISMARINE_BLOCK_TYPE, StringValues::PRISMARINE_BLOCK_TYPE_BRICKS));
		$this->map(Blocks::PRISMARINE_BRICKS_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PRISMARINE_BRICK));
		$this->mapStairs(Blocks::PRISMARINE_BRICKS_STAIRS(), Ids::PRISMARINE_BRICKS_STAIRS);
		$this->map(Blocks::PRISMARINE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PRISMARINE_ROUGH));
		$this->mapStairs(Blocks::PRISMARINE_STAIRS(), Ids::PRISMARINE_STAIRS);
		$this->map(Blocks::PRISMARINE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_PRISMARINE));
		$this->map(Blocks::PUMPKIN(), function() : Writer{
			return Writer::create(Ids::PUMPKIN)
				->writeCardinalHorizontalFacing(Facing::SOUTH); //no longer used
		});
		$this->map(Blocks::PUMPKIN_STEM(), fn(PumpkinStem $block) => Helper::encodeStem($block, new Writer(Ids::PUMPKIN_STEM)));
		$this->map(Blocks::PURPLE_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, true, Writer::create(Ids::COLORED_TORCH_BP)));
		$this->map(Blocks::PURPUR(), function() : Writer{
			return Writer::create(Ids::PURPUR_BLOCK)
				->writeString(StateNames::CHISEL_TYPE, StringValues::CHISEL_TYPE_DEFAULT)
				->writePillarAxis(Axis::Y); //useless, but MCPE wants it
		});
		$this->map(Blocks::PURPUR_PILLAR(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::PURPUR_BLOCK)
				->writeString(StateNames::CHISEL_TYPE, StringValues::CHISEL_TYPE_LINES)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::PURPUR_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PURPUR));
		$this->mapStairs(Blocks::PURPUR_STAIRS(), Ids::PURPUR_STAIRS);
		$this->map(Blocks::QUARTZ(), fn() => Helper::encodeQuartz(StringValues::CHISEL_TYPE_DEFAULT, Axis::Y));
		$this->map(Blocks::QUARTZ_PILLAR(), fn(SimplePillar $block) => Helper::encodeQuartz(StringValues::CHISEL_TYPE_LINES, $block->getAxis()));
		$this->map(Blocks::QUARTZ_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_QUARTZ));
		$this->mapStairs(Blocks::QUARTZ_STAIRS(), Ids::QUARTZ_STAIRS);
		$this->map(Blocks::RAIL(), function(Rail $block) : Writer{
			return Writer::create(Ids::RAIL)
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		// Bedrock piston base uses reversed horizontal direction indices; its
		// collision head uses reversed indices for all six faces.
		$this->map(Blocks::PISTON(), fn(Piston $b) => Writer::create(Ids::PISTON)->writeFacingDirection(Piston::networkFacing($b->getFacing())));
		$this->map(Blocks::STICKY_PISTON(), fn(Piston $b) => Writer::create(Ids::STICKY_PISTON)->writeFacingDirection(Piston::networkFacing($b->getFacing())));
		$this->map(Blocks::PISTON_ARM(), fn(PistonHead $b) => Writer::create(Ids::PISTON_ARM_COLLISION)->writeFacingDirection(Facing::opposite($b->getFacing())));
		$this->map(Blocks::STICKY_PISTON_ARM(), fn(PistonHead $b) => Writer::create(Ids::STICKY_PISTON_ARM_COLLISION)->writeFacingDirection(Facing::opposite($b->getFacing())));
		$this->map(Blocks::REDSTONE_COMPARATOR(), function(RedstoneComparator $block) : Writer{
			return Writer::create($block->isPowered() ? Ids::POWERED_COMPARATOR : Ids::UNPOWERED_COMPARATOR)
				->writeBool(StateNames::OUTPUT_LIT_BIT, $block->isPowered())
				->writeBool(StateNames::OUTPUT_SUBTRACT_BIT, $block->isSubtractMode())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::REDSTONE_LAMP(), fn(RedstoneLamp $block) => new Writer($block->isPowered() ? Ids::LIT_REDSTONE_LAMP : Ids::REDSTONE_LAMP));
		$this->map(Blocks::REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_REDSTONE_ORE : Ids::REDSTONE_ORE));
		$this->map(Blocks::REDSTONE_REPEATER(), function(RedstoneRepeater $block) : Writer{
			return Writer::create($block->isPowered() ? Ids::POWERED_REPEATER : Ids::UNPOWERED_REPEATER)
				->writeLegacyHorizontalFacing($block->getFacing())
				->writeInt(StateNames::REPEATER_DELAY, $block->getDelay() - 1);
		});
		$this->map(Blocks::REDSTONE_TORCH(), function(RedstoneTorch $block) : Writer{
			return Writer::create($block->isLit() ? Ids::REDSTONE_TORCH : Ids::UNLIT_REDSTONE_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::REDSTONE_WIRE(), function(RedstoneWire $block) : Writer{
			return Writer::create(Ids::REDSTONE_WIRE)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::RED_MUSHROOM_BLOCK(), fn(RedMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::RED_MUSHROOM_BLOCK)));
		$this->map(Blocks::RED_NETHER_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_RED_NETHER_BRICK));
		$this->mapStairs(Blocks::RED_NETHER_BRICK_STAIRS(), Ids::RED_NETHER_BRICK_STAIRS);
		$this->map(Blocks::RED_NETHER_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_RED_NETHER_BRICK));
		$this->map(Blocks::RED_SAND(), fn() => Writer::create(Ids::SAND)
				->writeString(StateNames::SAND_TYPE, StringValues::SAND_TYPE_RED));
		$this->map(Blocks::RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_DEFAULT));
		$this->map(Blocks::RED_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_RED_SANDSTONE));
		$this->mapStairs(Blocks::RED_SANDSTONE_STAIRS(), Ids::RED_SANDSTONE_STAIRS);
		$this->map(Blocks::RED_SANDSTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_RED_SANDSTONE));
		$this->map(Blocks::RED_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, false, Writer::create(Ids::COLORED_TORCH_RG)));
		$this->map(Blocks::RED_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_RED));
		$this->map(Blocks::ROSE_BUSH(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_ROSE, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(Blocks::SAND(), fn() => Writer::create(Ids::SAND)
				->writeString(StateNames::SAND_TYPE, StringValues::SAND_TYPE_NORMAL));
		$this->map(Blocks::SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_DEFAULT));
		$this->map(Blocks::SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_SANDSTONE));
		$this->mapStairs(Blocks::SANDSTONE_STAIRS(), Ids::SANDSTONE_STAIRS);
		$this->map(Blocks::SANDSTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_SANDSTONE));
		$this->map(Blocks::SEA_PICKLE(), function(SeaPickle $block) : Writer{
			return Writer::create(Ids::SEA_PICKLE)
				->writeBool(StateNames::DEAD_BIT, !$block->isUnderwater())
				->writeInt(StateNames::CLUSTER_COUNT, $block->getCount() - 1);
		});
		$this->map(Blocks::SMOKER(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::SMOKER, Ids::LIT_SMOKER));
		$this->map(Blocks::SMOOTH_QUARTZ(), fn() => Helper::encodeQuartz(StringValues::CHISEL_TYPE_SMOOTH, Axis::Y));
		$this->map(Blocks::SMOOTH_QUARTZ_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_SMOOTH_QUARTZ));
		$this->mapStairs(Blocks::SMOOTH_QUARTZ_STAIRS(), Ids::SMOOTH_QUARTZ_STAIRS);
		$this->map(Blocks::SMOOTH_RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_SMOOTH));
		$this->map(Blocks::SMOOTH_RED_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_SMOOTH_RED_SANDSTONE));
		$this->mapStairs(Blocks::SMOOTH_RED_SANDSTONE_STAIRS(), Ids::SMOOTH_RED_SANDSTONE_STAIRS);
		$this->map(Blocks::SMOOTH_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_SMOOTH));
		$this->map(Blocks::SMOOTH_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_SMOOTH_SANDSTONE));
		$this->mapStairs(Blocks::SMOOTH_SANDSTONE_STAIRS(), Ids::SMOOTH_SANDSTONE_STAIRS);
		$this->map(Blocks::SMOOTH_STONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_SMOOTH_STONE));
		$this->map(Blocks::SNOW_LAYER(), function(SnowLayer $block) : Writer{
			return Writer::create(Ids::SNOW_LAYER)
				->writeBool(StateNames::COVERED_BIT, false)
				->writeInt(StateNames::HEIGHT, $block->getLayers() - 1);
		});
		$this->map(Blocks::SOUL_FIRE(), function() : Writer{
			return Writer::create(Ids::SOUL_FIRE)
				->writeInt(StateNames::AGE, 0); //useless for soul fire, we don't track it
		});
		$this->map(Blocks::SOUL_LANTERN(), function(Lantern $block) : Writer{
			return Writer::create(Ids::SOUL_LANTERN)
				->writeBool(StateNames::HANGING, $block->isHanging());
		});
		$this->map(Blocks::SOUL_TORCH(), function(Torch $block) : Writer{
			return Writer::create(Ids::SOUL_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::SPONGE(), function(Sponge $block) : Writer{
			return Writer::create(Ids::SPONGE)
				->writeString(StateNames::SPONGE_TYPE, $block->isWet() ? StringValues::SPONGE_TYPE_WET : StringValues::SPONGE_TYPE_DRY);
		});
		$this->map(Blocks::SPRUCE_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_SPRUCE));
		$this->map(Blocks::STAINED_CLAY(), function(StainedHardenedClay $block) : Writer{
			return Writer::create(Ids::STAINED_HARDENED_CLAY)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::STAINED_GLASS(), function(StainedGlass $block) : Writer{
			return Writer::create(Ids::STAINED_GLASS)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::STAINED_GLASS_PANE(), function(StainedGlassPane $block) : Writer{
			return Writer::create(Ids::STAINED_GLASS_PANE)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::STAINED_HARDENED_GLASS(), function(StainedHardenedGlass $block) : Writer{
			return Writer::create(Ids::HARD_STAINED_GLASS)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::STAINED_HARDENED_GLASS_PANE(), function(StainedHardenedGlassPane $block) : Writer{
			return Writer::create(Ids::HARD_STAINED_GLASS_PANE)
				->writeColor($block->getColor());
		});
		$this->map(Blocks::STONE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_STONE));
		$this->map(Blocks::STONECUTTER(), fn(Stonecutter $block) => Writer::create(Ids::STONECUTTER_BLOCK)
			->writeHorizontalFacing($block->getFacing()));
		$this->map(Blocks::STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_DEFAULT));
		$this->map(Blocks::STONE_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_STONE_BRICK));
		$this->mapStairs(Blocks::STONE_BRICK_STAIRS(), Ids::STONE_BRICK_STAIRS);
		$this->map(Blocks::STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_STONE_BRICK));
		$this->map(Blocks::STONE_BUTTON(), fn(StoneButton $block) => Helper::encodeButton($block, new Writer(Ids::STONE_BUTTON)));
		$this->map(Blocks::STONE_PRESSURE_PLATE(), fn(StonePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::STONE_PRESSURE_PLATE)));
		$this->map(Blocks::STONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_STONE));
		$this->mapStairs(Blocks::STONE_STAIRS(), Ids::NORMAL_STONE_STAIRS);
		$this->map(Blocks::SUGARCANE(), function(Sugarcane $block) : Writer{
			return Writer::create(Ids::REEDS)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::SUNFLOWER(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_SUNFLOWER, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(Blocks::SWEET_BERRY_BUSH(), function(SweetBerryBush $block) : Writer{
			return Writer::create(Ids::SWEET_BERRY_BUSH)
				->writeInt(StateNames::GROWTH, $block->getAge());
		});
		$this->map(Blocks::TALL_GRASS(), fn() => Writer::create(Ids::TALLGRASS)
				->writeString(StateNames::TALL_GRASS_TYPE, StringValues::TALL_GRASS_TYPE_TALL));
		$this->map(Blocks::TNT(), function(TNT $block) : Writer{
			return Writer::create(Ids::TNT)
				->writeBool(StateNames::ALLOW_UNDERWATER_BIT, $block->worksUnderwater())
				->writeBool(StateNames::EXPLODE_BIT, $block->isUnstable());
		});
		$this->map(Blocks::TORCH(), function(Torch $block) : Writer{
			return Writer::create(Ids::TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::TRAPPED_CHEST(), function(TrappedChest $block) : Writer{
			return Writer::create(Ids::TRAPPED_CHEST)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::TRIPWIRE(), function(Tripwire $block) : Writer{
			return Writer::create(Ids::TRIP_WIRE)
				->writeBool(StateNames::ATTACHED_BIT, $block->isConnected())
				->writeBool(StateNames::DISARMED_BIT, $block->isDisarmed())
				->writeBool(StateNames::POWERED_BIT, $block->isTriggered())
				->writeBool(StateNames::SUSPENDED_BIT, $block->isSuspended());
		});
		$this->map(Blocks::TRIPWIRE_HOOK(), function(TripwireHook $block) : Writer{
			return Writer::create(Ids::TRIPWIRE_HOOK)
				->writeBool(StateNames::ATTACHED_BIT, $block->isConnected())
				->writeBool(StateNames::POWERED_BIT, $block->isPowered())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::TWISTING_VINES(), function(NetherVines $block) : Writer{
			return Writer::create(Ids::TWISTING_VINES)
				->writeInt(StateNames::TWISTING_VINES_AGE, $block->getAge());
		});
		$this->map(Blocks::VINES(), function(Vine $block) : Writer{
			return Writer::create(Ids::VINE)
				->writeInt(StateNames::VINE_DIRECTION_BITS, ($block->hasFace(Facing::NORTH) ? BlockLegacyMetadata::VINE_FLAG_NORTH : 0) | ($block->hasFace(Facing::SOUTH) ? BlockLegacyMetadata::VINE_FLAG_SOUTH : 0) | ($block->hasFace(Facing::WEST) ? BlockLegacyMetadata::VINE_FLAG_WEST : 0) | ($block->hasFace(Facing::EAST) ? BlockLegacyMetadata::VINE_FLAG_EAST : 0));
		});
		$this->map(Blocks::WALL_BANNER(), function(WallBanner $block) : Writer{
			return Writer::create(Ids::WALL_BANNER)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::WALL_CORAL_FAN(), function(WallCoralFan $block) : Writer{
			$coralType = $block->getCoralType();
			return Writer::create(match($coralType->id()){
				CoralType::TUBE()->id(), CoralType::BRAIN()->id() => Ids::CORAL_FAN_HANG,
				CoralType::BUBBLE()->id(), CoralType::FIRE()->id() => Ids::CORAL_FAN_HANG2,
				CoralType::HORN()->id() => Ids::CORAL_FAN_HANG3,
				default => throw new BlockStateSerializeException("Invalid Coral type " . $coralType->name()),
			})
				->writeBool(StateNames::CORAL_HANG_TYPE_BIT, $coralType->equals(CoralType::BRAIN()) || $coralType->equals(CoralType::FIRE()))
				->writeBool(StateNames::DEAD_BIT, $block->isDead())
				->writeCoralFacing($block->getFacing());
		});
		$this->map(Blocks::WATER(), fn(Water $block) => Helper::encodeLiquid($block, Ids::WATER, Ids::FLOWING_WATER));
		$this->map(Blocks::WEEPING_VINES(), function(NetherVines $block) : Writer{
			return Writer::create(Ids::WEEPING_VINES)
				->writeInt(StateNames::WEEPING_VINES_AGE, $block->getAge());
		});
		$this->map(Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), function(WeightedPressurePlateHeavy $block) : Writer{
			return Writer::create(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), function(WeightedPressurePlateLight $block) : Writer{
			return Writer::create(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::WHEAT(), fn(Wheat $block) => Helper::encodeCrops($block, new Writer(Ids::WHEAT)));
		$this->map(Blocks::WHITE_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_WHITE));
	}
}
