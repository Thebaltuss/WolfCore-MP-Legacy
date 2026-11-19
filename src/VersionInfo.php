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
 * @maintainer Thebaltusss (WolfCore-MP)
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace wolfcore;

use wolfcore\utils\Git;
use wolfcore\utils\VersionString;
use function is_array;
use function is_int;
use function str_repeat;

final class VersionInfo{
	public const NAME = "WolfCore-MP";
	public const OWNER = "Thebaltusss";
	public const BASE_VERSION = "1.0.0";
	public const IS_DEVELOPMENT_BUILD = false;
	public const BUILD_CHANNEL = "stable";

	/**
	 * WolfCore-MP-specific version ID for world data. Used to determine what fixes need to be applied to old world
	 * data (e.g. stuff saved wrongly by past versions).
	 * This version supplements the Minecraft vanilla world version.
	 *
	 * This should be bumped if any **non-Mojang** BC-breaking change or bug fix is made to world save data of any kind
	 * (entities, tiles, blocks, biomes etc.). For example, if PM accidentally saved a block with its facing value
	 * swapped, we would bump this, but not if Mojang did the same change.
	 */
	public const WORLD_DATA_VERSION = 1;
	/**
	 * Name of the NBT tag used to store the world data version.
	 */
	public const TAG_WORLD_DATA_VERSION = "PMMPDataVersion"; //TAG_Long

	private function __construct(){
		//NOOP
	}

	/**
	 * Locate the PHAR from this source file, even when the bootstrap is required
	 * through phar:// by an external launcher instead of executing the stub.
	 * Phar::running() alone may not identify the archive in such a context.
	 *
	 * @return array<string, mixed>|null
	 */
	private static function getArchiveMetadata() : ?array{
		if(str_starts_with(__FILE__, "phar://")){
			// __FILE__ = phar://.../WolfCore-MP.phar/src/VersionInfo.php
			$archivePath = substr(dirname(__DIR__), strlen("phar://"));
		}else{
			$archivePath = \Phar::running(false);
		}
		if($archivePath === ""){
			return null;
		}
		$metadata = (new \Phar($archivePath))->getMetadata();
		return is_array($metadata) ? $metadata : null;
	}

	private static ?string $gitHash = null;

	public static function GIT_HASH() : string{
		if(self::$gitHash === null){
			$metadata = self::getArchiveMetadata();
			if($metadata !== null){
				self::$gitHash = isset($metadata["git"]) && is_string($metadata["git"])
					? $metadata["git"] : str_repeat("00", 20);
			}else{
				self::$gitHash = Git::getRepositoryStatePretty(\wolfcore\PATH);
			}
		}

		return self::$gitHash;
	}

	private static ?int $buildNumber = null;

	public static function BUILD_NUMBER() : int{
		if(self::$buildNumber === null){
			$metadata = self::getArchiveMetadata();
			self::$buildNumber = $metadata !== null && isset($metadata["build"]) && is_int($metadata["build"])
				? $metadata["build"] : 0;
		}
		return self::$buildNumber;
	}

	private static ?VersionString $fullVersion = null;

	public static function VERSION() : VersionString{
		if(self::$fullVersion === null){
			self::$fullVersion = new VersionString(self::BASE_VERSION, self::IS_DEVELOPMENT_BUILD, self::BUILD_NUMBER());
		}
		return self::$fullVersion;
	}
}
