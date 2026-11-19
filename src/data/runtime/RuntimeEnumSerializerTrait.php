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

namespace wolfcore\data\runtime;

/**
 * This class is auto-generated. Do not edit it manually.
 * @see build/generate-runtime-enum-serializers.php
 */
trait RuntimeEnumSerializerTrait{

	abstract protected function writeInt(int $bits, int $value) : void;

	public function bellAttachmentType(\wolfcore\block\utils\BellAttachmentType &$value) : void{
		$this->writeInt(2, match($value){
			\wolfcore\block\utils\BellAttachmentType::CEILING() => 0,
			\wolfcore\block\utils\BellAttachmentType::FLOOR() => 1,
			\wolfcore\block\utils\BellAttachmentType::ONE_WALL() => 2,
			\wolfcore\block\utils\BellAttachmentType::TWO_WALLS() => 3,
			default => throw new \wolfcore\utils\AssumptionFailedError("All BellAttachmentType cases should be covered")
		});
	}

	public function copperOxidation(\wolfcore\block\utils\CopperOxidation &$value) : void{
		$this->writeInt(2, match($value){
			\wolfcore\block\utils\CopperOxidation::EXPOSED() => 0,
			\wolfcore\block\utils\CopperOxidation::NONE() => 1,
			\wolfcore\block\utils\CopperOxidation::OXIDIZED() => 2,
			\wolfcore\block\utils\CopperOxidation::WEATHERED() => 3,
			default => throw new \wolfcore\utils\AssumptionFailedError("All CopperOxidation cases should be covered")
		});
	}

	public function coralType(\wolfcore\block\utils\CoralType &$value) : void{
		$this->writeInt(3, match($value){
			\wolfcore\block\utils\CoralType::BRAIN() => 0,
			\wolfcore\block\utils\CoralType::BUBBLE() => 1,
			\wolfcore\block\utils\CoralType::FIRE() => 2,
			\wolfcore\block\utils\CoralType::HORN() => 3,
			\wolfcore\block\utils\CoralType::TUBE() => 4,
			default => throw new \wolfcore\utils\AssumptionFailedError("All CoralType cases should be covered")
		});
	}

	public function dirtType(\wolfcore\block\utils\DirtType &$value) : void{
		$this->writeInt(2, match($value){
			\wolfcore\block\utils\DirtType::COARSE() => 0,
			\wolfcore\block\utils\DirtType::NORMAL() => 1,
			\wolfcore\block\utils\DirtType::ROOTED() => 2,
			default => throw new \wolfcore\utils\AssumptionFailedError("All DirtType cases should be covered")
		});
	}

	public function dyeColor(\wolfcore\block\utils\DyeColor &$value) : void{
		$this->writeInt(4, match($value){
			\wolfcore\block\utils\DyeColor::BLACK() => 0,
			\wolfcore\block\utils\DyeColor::BLUE() => 1,
			\wolfcore\block\utils\DyeColor::BROWN() => 2,
			\wolfcore\block\utils\DyeColor::CYAN() => 3,
			\wolfcore\block\utils\DyeColor::GRAY() => 4,
			\wolfcore\block\utils\DyeColor::GREEN() => 5,
			\wolfcore\block\utils\DyeColor::LIGHT_BLUE() => 6,
			\wolfcore\block\utils\DyeColor::LIGHT_GRAY() => 7,
			\wolfcore\block\utils\DyeColor::LIME() => 8,
			\wolfcore\block\utils\DyeColor::MAGENTA() => 9,
			\wolfcore\block\utils\DyeColor::ORANGE() => 10,
			\wolfcore\block\utils\DyeColor::PINK() => 11,
			\wolfcore\block\utils\DyeColor::PURPLE() => 12,
			\wolfcore\block\utils\DyeColor::RED() => 13,
			\wolfcore\block\utils\DyeColor::WHITE() => 14,
			\wolfcore\block\utils\DyeColor::YELLOW() => 15,
			default => throw new \wolfcore\utils\AssumptionFailedError("All DyeColor cases should be covered")
		});
	}

	public function froglightType(\wolfcore\block\utils\FroglightType &$value) : void{
		$this->writeInt(2, match($value){
			\wolfcore\block\utils\FroglightType::OCHRE() => 0,
			\wolfcore\block\utils\FroglightType::PEARLESCENT() => 1,
			\wolfcore\block\utils\FroglightType::VERDANT() => 2,
			default => throw new \wolfcore\utils\AssumptionFailedError("All FroglightType cases should be covered")
		});
	}

	public function leverFacing(\wolfcore\block\utils\LeverFacing &$value) : void{
		$this->writeInt(3, match($value){
			\wolfcore\block\utils\LeverFacing::DOWN_AXIS_X() => 0,
			\wolfcore\block\utils\LeverFacing::DOWN_AXIS_Z() => 1,
			\wolfcore\block\utils\LeverFacing::EAST() => 2,
			\wolfcore\block\utils\LeverFacing::NORTH() => 3,
			\wolfcore\block\utils\LeverFacing::SOUTH() => 4,
			\wolfcore\block\utils\LeverFacing::UP_AXIS_X() => 5,
			\wolfcore\block\utils\LeverFacing::UP_AXIS_Z() => 6,
			\wolfcore\block\utils\LeverFacing::WEST() => 7,
			default => throw new \wolfcore\utils\AssumptionFailedError("All LeverFacing cases should be covered")
		});
	}

	public function mobHeadType(\wolfcore\block\utils\MobHeadType &$value) : void{
		$this->writeInt(3, match($value){
			\wolfcore\block\utils\MobHeadType::CREEPER() => 0,
			\wolfcore\block\utils\MobHeadType::DRAGON() => 1,
			\wolfcore\block\utils\MobHeadType::PIGLIN() => 2,
			\wolfcore\block\utils\MobHeadType::PLAYER() => 3,
			\wolfcore\block\utils\MobHeadType::SKELETON() => 4,
			\wolfcore\block\utils\MobHeadType::WITHER_SKELETON() => 5,
			\wolfcore\block\utils\MobHeadType::ZOMBIE() => 6,
			default => throw new \wolfcore\utils\AssumptionFailedError("All MobHeadType cases should be covered")
		});
	}

	public function mushroomBlockType(\wolfcore\block\utils\MushroomBlockType &$value) : void{
		$this->writeInt(4, match($value){
			\wolfcore\block\utils\MushroomBlockType::ALL_CAP() => 0,
			\wolfcore\block\utils\MushroomBlockType::CAP_EAST() => 1,
			\wolfcore\block\utils\MushroomBlockType::CAP_MIDDLE() => 2,
			\wolfcore\block\utils\MushroomBlockType::CAP_NORTH() => 3,
			\wolfcore\block\utils\MushroomBlockType::CAP_NORTHEAST() => 4,
			\wolfcore\block\utils\MushroomBlockType::CAP_NORTHWEST() => 5,
			\wolfcore\block\utils\MushroomBlockType::CAP_SOUTH() => 6,
			\wolfcore\block\utils\MushroomBlockType::CAP_SOUTHEAST() => 7,
			\wolfcore\block\utils\MushroomBlockType::CAP_SOUTHWEST() => 8,
			\wolfcore\block\utils\MushroomBlockType::CAP_WEST() => 9,
			\wolfcore\block\utils\MushroomBlockType::PORES() => 10,
			default => throw new \wolfcore\utils\AssumptionFailedError("All MushroomBlockType cases should be covered")
		});
	}

	public function potionType(\wolfcore\item\PotionType &$value) : void{
		$this->writeInt(6, match($value){
			\wolfcore\item\PotionType::AWKWARD() => 0,
			\wolfcore\item\PotionType::FIRE_RESISTANCE() => 1,
			\wolfcore\item\PotionType::HARMING() => 2,
			\wolfcore\item\PotionType::HEALING() => 3,
			\wolfcore\item\PotionType::INVISIBILITY() => 4,
			\wolfcore\item\PotionType::LEAPING() => 5,
			\wolfcore\item\PotionType::LONG_FIRE_RESISTANCE() => 6,
			\wolfcore\item\PotionType::LONG_INVISIBILITY() => 7,
			\wolfcore\item\PotionType::LONG_LEAPING() => 8,
			\wolfcore\item\PotionType::LONG_MUNDANE() => 9,
			\wolfcore\item\PotionType::LONG_NIGHT_VISION() => 10,
			\wolfcore\item\PotionType::LONG_POISON() => 11,
			\wolfcore\item\PotionType::LONG_REGENERATION() => 12,
			\wolfcore\item\PotionType::LONG_SLOWNESS() => 13,
			\wolfcore\item\PotionType::LONG_SLOW_FALLING() => 14,
			\wolfcore\item\PotionType::LONG_STRENGTH() => 15,
			\wolfcore\item\PotionType::LONG_SWIFTNESS() => 16,
			\wolfcore\item\PotionType::LONG_TURTLE_MASTER() => 17,
			\wolfcore\item\PotionType::LONG_WATER_BREATHING() => 18,
			\wolfcore\item\PotionType::LONG_WEAKNESS() => 19,
			\wolfcore\item\PotionType::MUNDANE() => 20,
			\wolfcore\item\PotionType::NIGHT_VISION() => 21,
			\wolfcore\item\PotionType::POISON() => 22,
			\wolfcore\item\PotionType::REGENERATION() => 23,
			\wolfcore\item\PotionType::SLOWNESS() => 24,
			\wolfcore\item\PotionType::SLOW_FALLING() => 25,
			\wolfcore\item\PotionType::STRENGTH() => 26,
			\wolfcore\item\PotionType::STRONG_HARMING() => 27,
			\wolfcore\item\PotionType::STRONG_HEALING() => 28,
			\wolfcore\item\PotionType::STRONG_LEAPING() => 29,
			\wolfcore\item\PotionType::STRONG_POISON() => 30,
			\wolfcore\item\PotionType::STRONG_REGENERATION() => 31,
			\wolfcore\item\PotionType::STRONG_STRENGTH() => 32,
			\wolfcore\item\PotionType::STRONG_SWIFTNESS() => 33,
			\wolfcore\item\PotionType::STRONG_TURTLE_MASTER() => 34,
			\wolfcore\item\PotionType::SWIFTNESS() => 35,
			\wolfcore\item\PotionType::THICK() => 36,
			\wolfcore\item\PotionType::TURTLE_MASTER() => 37,
			\wolfcore\item\PotionType::WATER() => 38,
			\wolfcore\item\PotionType::WATER_BREATHING() => 39,
			\wolfcore\item\PotionType::WEAKNESS() => 40,
			\wolfcore\item\PotionType::WITHER() => 41,
			default => throw new \wolfcore\utils\AssumptionFailedError("All PotionType cases should be covered")
		});
	}

	public function slabType(\wolfcore\block\utils\SlabType &$value) : void{
		$this->writeInt(2, match($value){
			\wolfcore\block\utils\SlabType::BOTTOM() => 0,
			\wolfcore\block\utils\SlabType::DOUBLE() => 1,
			\wolfcore\block\utils\SlabType::TOP() => 2,
			default => throw new \wolfcore\utils\AssumptionFailedError("All SlabType cases should be covered")
		});
	}

	public function suspiciousStewType(\wolfcore\item\SuspiciousStewType &$value) : void{
		$this->writeInt(4, match($value){
			\wolfcore\item\SuspiciousStewType::ALLIUM() => 0,
			\wolfcore\item\SuspiciousStewType::AZURE_BLUET() => 1,
			\wolfcore\item\SuspiciousStewType::BLUE_ORCHID() => 2,
			\wolfcore\item\SuspiciousStewType::CORNFLOWER() => 3,
			\wolfcore\item\SuspiciousStewType::DANDELION() => 4,
			\wolfcore\item\SuspiciousStewType::LILY_OF_THE_VALLEY() => 5,
			\wolfcore\item\SuspiciousStewType::OXEYE_DAISY() => 6,
			\wolfcore\item\SuspiciousStewType::POPPY() => 7,
			\wolfcore\item\SuspiciousStewType::TULIP() => 8,
			\wolfcore\item\SuspiciousStewType::WITHER_ROSE() => 9,
			default => throw new \wolfcore\utils\AssumptionFailedError("All SuspiciousStewType cases should be covered")
		});
	}

}
