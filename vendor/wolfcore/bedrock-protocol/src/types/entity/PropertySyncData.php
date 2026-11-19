<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol\types\entity;

use wolfcore\network\mcpe\protocol\PacketDecodeException;
use wolfcore\network\mcpe\protocol\serializer\PacketSerializer;
use function count;

final class PropertySyncData{
	// Total number of decoded integer and float actor properties.
	private const MAX_SYNCED_PROPERTIES = 256;

	/**
	 * @param int[] $intProperties
	 * @param float[] $floatProperties
	 * @phpstan-param array<int, int> $intProperties
	 * @phpstan-param array<int, float> $floatProperties
	 */
	public function __construct(
		private array $intProperties,
		private array $floatProperties,
	){}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getIntProperties() : array{
		return $this->intProperties;
	}

	/**
	 * @return float[]
	 * @phpstan-return array<int, float>
	 */
	public function getFloatProperties() : array{
		return $this->floatProperties;
	}

	public static function read(PacketSerializer $in) : self{
		$intProperties = [];
		$floatProperties = [];

		$intCount = $in->getUnsignedVarInt();
		if($intCount > self::MAX_SYNCED_PROPERTIES){
			throw new PacketDecodeException("Too many integer actor properties ($intCount)");
		}
		for($i = 0; $i < $intCount; ++$i){
			$intProperties[$in->getUnsignedVarInt()] = $in->getVarInt();
		}

		$floatCount = $in->getUnsignedVarInt();
		if($floatCount > self::MAX_SYNCED_PROPERTIES - $intCount){
			throw new PacketDecodeException("Too many synchronized actor properties (limit " . self::MAX_SYNCED_PROPERTIES . ")");
		}
		for($i = 0; $i < $floatCount; ++$i){
			$floatProperties[$in->getUnsignedVarInt()] = $in->getLFloat();
		}

		return new self($intProperties, $floatProperties);
	}

	public function write(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->intProperties));
		foreach($this->intProperties as $key => $value){
			$out->putUnsignedVarInt($key);
			$out->putVarInt($value);
		}
		$out->putUnsignedVarInt(count($this->floatProperties));
		foreach($this->floatProperties as $key => $value){
			$out->putUnsignedVarInt($key);
			$out->putLFloat($value);
		}
	}
}
