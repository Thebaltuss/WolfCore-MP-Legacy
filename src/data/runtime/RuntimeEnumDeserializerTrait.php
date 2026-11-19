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
trait RuntimeEnumDeserializerTrait{

	abstract protected function readInt(int $bits) : int;

	public function bellAttachmentType(\wolfcore\block\utils\BellAttachmentType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \wolfcore\block\utils\BellAttachmentType::CEILING(),
			1 => \wolfcore\block\utils\BellAttachmentType::FLOOR(),
			2 => \wolfcore\block\utils\BellAttachmentType::ONE_WALL(),
			3 => \wolfcore\block\utils\BellAttachmentType::TWO_WALLS(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for BellAttachmentType")
		};
	}

	public function copperOxidation(\wolfcore\block\utils\CopperOxidation &$value) : void{
		$value = match($this->readInt(2)){
			0 => \wolfcore\block\utils\CopperOxidation::EXPOSED(),
			1 => \wolfcore\block\utils\CopperOxidation::NONE(),
			2 => \wolfcore\block\utils\CopperOxidation::OXIDIZED(),
			3 => \wolfcore\block\utils\CopperOxidation::WEATHERED(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for CopperOxidation")
		};
	}

	public function coralType(\wolfcore\block\utils\CoralType &$value) : void{
		$value = match($this->readInt(3)){
			0 => \wolfcore\block\utils\CoralType::BRAIN(),
			1 => \wolfcore\block\utils\CoralType::BUBBLE(),
			2 => \wolfcore\block\utils\CoralType::FIRE(),
			3 => \wolfcore\block\utils\CoralType::HORN(),
			4 => \wolfcore\block\utils\CoralType::TUBE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for CoralType")
		};
	}

	public function dirtType(\wolfcore\block\utils\DirtType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \wolfcore\block\utils\DirtType::COARSE(),
			1 => \wolfcore\block\utils\DirtType::NORMAL(),
			2 => \wolfcore\block\utils\DirtType::ROOTED(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for DirtType")
		};
	}

	public function dyeColor(\wolfcore\block\utils\DyeColor &$value) : void{
		$value = match($this->readInt(4)){
			0 => \wolfcore\block\utils\DyeColor::BLACK(),
			1 => \wolfcore\block\utils\DyeColor::BLUE(),
			2 => \wolfcore\block\utils\DyeColor::BROWN(),
			3 => \wolfcore\block\utils\DyeColor::CYAN(),
			4 => \wolfcore\block\utils\DyeColor::GRAY(),
			5 => \wolfcore\block\utils\DyeColor::GREEN(),
			6 => \wolfcore\block\utils\DyeColor::LIGHT_BLUE(),
			7 => \wolfcore\block\utils\DyeColor::LIGHT_GRAY(),
			8 => \wolfcore\block\utils\DyeColor::LIME(),
			9 => \wolfcore\block\utils\DyeColor::MAGENTA(),
			10 => \wolfcore\block\utils\DyeColor::ORANGE(),
			11 => \wolfcore\block\utils\DyeColor::PINK(),
			12 => \wolfcore\block\utils\DyeColor::PURPLE(),
			13 => \wolfcore\block\utils\DyeColor::RED(),
			14 => \wolfcore\block\utils\DyeColor::WHITE(),
			15 => \wolfcore\block\utils\DyeColor::YELLOW(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for DyeColor")
		};
	}

	public function froglightType(\wolfcore\block\utils\FroglightType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \wolfcore\block\utils\FroglightType::OCHRE(),
			1 => \wolfcore\block\utils\FroglightType::PEARLESCENT(),
			2 => \wolfcore\block\utils\FroglightType::VERDANT(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for FroglightType")
		};
	}

	public function leverFacing(\wolfcore\block\utils\LeverFacing &$value) : void{
		$value = match($this->readInt(3)){
			0 => \wolfcore\block\utils\LeverFacing::DOWN_AXIS_X(),
			1 => \wolfcore\block\utils\LeverFacing::DOWN_AXIS_Z(),
			2 => \wolfcore\block\utils\LeverFacing::EAST(),
			3 => \wolfcore\block\utils\LeverFacing::NORTH(),
			4 => \wolfcore\block\utils\LeverFacing::SOUTH(),
			5 => \wolfcore\block\utils\LeverFacing::UP_AXIS_X(),
			6 => \wolfcore\block\utils\LeverFacing::UP_AXIS_Z(),
			7 => \wolfcore\block\utils\LeverFacing::WEST(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for LeverFacing")
		};
	}

	public function mobHeadType(\wolfcore\block\utils\MobHeadType &$value) : void{
		$value = match($this->readInt(3)){
			0 => \wolfcore\block\utils\MobHeadType::CREEPER(),
			1 => \wolfcore\block\utils\MobHeadType::DRAGON(),
			2 => \wolfcore\block\utils\MobHeadType::PIGLIN(),
			3 => \wolfcore\block\utils\MobHeadType::PLAYER(),
			4 => \wolfcore\block\utils\MobHeadType::SKELETON(),
			5 => \wolfcore\block\utils\MobHeadType::WITHER_SKELETON(),
			6 => \wolfcore\block\utils\MobHeadType::ZOMBIE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for MobHeadType")
		};
	}

	public function mushroomBlockType(\wolfcore\block\utils\MushroomBlockType &$value) : void{
		$value = match($this->readInt(4)){
			0 => \wolfcore\block\utils\MushroomBlockType::ALL_CAP(),
			1 => \wolfcore\block\utils\MushroomBlockType::CAP_EAST(),
			2 => \wolfcore\block\utils\MushroomBlockType::CAP_MIDDLE(),
			3 => \wolfcore\block\utils\MushroomBlockType::CAP_NORTH(),
			4 => \wolfcore\block\utils\MushroomBlockType::CAP_NORTHEAST(),
			5 => \wolfcore\block\utils\MushroomBlockType::CAP_NORTHWEST(),
			6 => \wolfcore\block\utils\MushroomBlockType::CAP_SOUTH(),
			7 => \wolfcore\block\utils\MushroomBlockType::CAP_SOUTHEAST(),
			8 => \wolfcore\block\utils\MushroomBlockType::CAP_SOUTHWEST(),
			9 => \wolfcore\block\utils\MushroomBlockType::CAP_WEST(),
			10 => \wolfcore\block\utils\MushroomBlockType::PORES(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for MushroomBlockType")
		};
	}

	public function potionType(\wolfcore\item\PotionType &$value) : void{
		$value = match($this->readInt(6)){
			0 => \wolfcore\item\PotionType::AWKWARD(),
			1 => \wolfcore\item\PotionType::FIRE_RESISTANCE(),
			2 => \wolfcore\item\PotionType::HARMING(),
			3 => \wolfcore\item\PotionType::HEALING(),
			4 => \wolfcore\item\PotionType::INVISIBILITY(),
			5 => \wolfcore\item\PotionType::LEAPING(),
			6 => \wolfcore\item\PotionType::LONG_FIRE_RESISTANCE(),
			7 => \wolfcore\item\PotionType::LONG_INVISIBILITY(),
			8 => \wolfcore\item\PotionType::LONG_LEAPING(),
			9 => \wolfcore\item\PotionType::LONG_MUNDANE(),
			10 => \wolfcore\item\PotionType::LONG_NIGHT_VISION(),
			11 => \wolfcore\item\PotionType::LONG_POISON(),
			12 => \wolfcore\item\PotionType::LONG_REGENERATION(),
			13 => \wolfcore\item\PotionType::LONG_SLOWNESS(),
			14 => \wolfcore\item\PotionType::LONG_SLOW_FALLING(),
			15 => \wolfcore\item\PotionType::LONG_STRENGTH(),
			16 => \wolfcore\item\PotionType::LONG_SWIFTNESS(),
			17 => \wolfcore\item\PotionType::LONG_TURTLE_MASTER(),
			18 => \wolfcore\item\PotionType::LONG_WATER_BREATHING(),
			19 => \wolfcore\item\PotionType::LONG_WEAKNESS(),
			20 => \wolfcore\item\PotionType::MUNDANE(),
			21 => \wolfcore\item\PotionType::NIGHT_VISION(),
			22 => \wolfcore\item\PotionType::POISON(),
			23 => \wolfcore\item\PotionType::REGENERATION(),
			24 => \wolfcore\item\PotionType::SLOWNESS(),
			25 => \wolfcore\item\PotionType::SLOW_FALLING(),
			26 => \wolfcore\item\PotionType::STRENGTH(),
			27 => \wolfcore\item\PotionType::STRONG_HARMING(),
			28 => \wolfcore\item\PotionType::STRONG_HEALING(),
			29 => \wolfcore\item\PotionType::STRONG_LEAPING(),
			30 => \wolfcore\item\PotionType::STRONG_POISON(),
			31 => \wolfcore\item\PotionType::STRONG_REGENERATION(),
			32 => \wolfcore\item\PotionType::STRONG_STRENGTH(),
			33 => \wolfcore\item\PotionType::STRONG_SWIFTNESS(),
			34 => \wolfcore\item\PotionType::STRONG_TURTLE_MASTER(),
			35 => \wolfcore\item\PotionType::SWIFTNESS(),
			36 => \wolfcore\item\PotionType::THICK(),
			37 => \wolfcore\item\PotionType::TURTLE_MASTER(),
			38 => \wolfcore\item\PotionType::WATER(),
			39 => \wolfcore\item\PotionType::WATER_BREATHING(),
			40 => \wolfcore\item\PotionType::WEAKNESS(),
			41 => \wolfcore\item\PotionType::WITHER(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for PotionType")
		};
	}

	public function slabType(\wolfcore\block\utils\SlabType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \wolfcore\block\utils\SlabType::BOTTOM(),
			1 => \wolfcore\block\utils\SlabType::DOUBLE(),
			2 => \wolfcore\block\utils\SlabType::TOP(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for SlabType")
		};
	}

	public function suspiciousStewType(\wolfcore\item\SuspiciousStewType &$value) : void{
		$value = match($this->readInt(4)){
			0 => \wolfcore\item\SuspiciousStewType::ALLIUM(),
			1 => \wolfcore\item\SuspiciousStewType::AZURE_BLUET(),
			2 => \wolfcore\item\SuspiciousStewType::BLUE_ORCHID(),
			3 => \wolfcore\item\SuspiciousStewType::CORNFLOWER(),
			4 => \wolfcore\item\SuspiciousStewType::DANDELION(),
			5 => \wolfcore\item\SuspiciousStewType::LILY_OF_THE_VALLEY(),
			6 => \wolfcore\item\SuspiciousStewType::OXEYE_DAISY(),
			7 => \wolfcore\item\SuspiciousStewType::POPPY(),
			8 => \wolfcore\item\SuspiciousStewType::TULIP(),
			9 => \wolfcore\item\SuspiciousStewType::WITHER_ROSE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for SuspiciousStewType")
		};
	}

}
