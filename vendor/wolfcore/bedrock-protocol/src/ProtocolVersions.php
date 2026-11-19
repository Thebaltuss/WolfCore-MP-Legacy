<?php

/*
 * WolfCore-MP's Bedrock client protocol registry.
 * Game data and world mechanics remain on BASE_PROTOCOL (1.20.0 / 589).
 * Adding entries for future protocols is NOT sufficient when their packet
 * formats, palettes or runtime ID mappings differ: add an adapter and tests.
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\protocol;

final class ProtocolVersions{
	private function __construct(){}

	public const BASE_PROTOCOL = 589;
	public const LATEST_PROTOCOL = 649;

	/**
	 * @var array<int, array{minecraftVersion: string, chainedCommands: bool, hashedBlockIds: bool}>
	 */
	private const VERSIONS = [
		589 => ["minecraftVersion" => "1.20.0", "chainedCommands" => false, "hashedBlockIds" => false],
		594 => ["minecraftVersion" => "1.20.10", "chainedCommands" => true, "hashedBlockIds" => true],
		618 => ["minecraftVersion" => "1.20.30", "chainedCommands" => true, "hashedBlockIds" => true],
		622 => ["minecraftVersion" => "1.20.40", "chainedCommands" => true, "hashedBlockIds" => true], // Also 1.20.41
		630 => ["minecraftVersion" => "1.20.50", "chainedCommands" => true, "hashedBlockIds" => true], // Also 1.20.51
		649 => ["minecraftVersion" => "1.20.60", "chainedCommands" => true, "hashedBlockIds" => true], // Also 1.20.61 and 1.20.62
	];

	/** @return int[] */
	public static function all() : array{
		return array_keys(self::VERSIONS);
	}

	public static function isSupported(int $protocol) : bool{
		return isset(self::VERSIONS[$protocol]);
	}

	public static function getMinecraftVersion(int $protocol) : string{
		return self::getDefinition($protocol)["minecraftVersion"];
	}

	/** Whether the 1.20.10+ AvailableCommands fields are present on the wire. */
	public static function hasChainedCommands(int $protocol) : bool{
		return self::getDefinition($protocol)["chainedCommands"];
	}

    /**
     * Isolated item palettes per wire protocol. 630 has a distinct item
     * dictionary (planks / stone variants were flattened in 1.20.50).
     */
    public static function getItemPaletteProtocol(int $protocol) : int{
        self::getDefinition($protocol);
        return $protocol >= 649 ? 649 : ($protocol >= 630 ? 630 : ($protocol >= 618 ? 618 : ($protocol >= 594 ? 594 : self::BASE_PROTOCOL)));
    }

	public static function usesHashedBlockIds(int $protocol) : bool{
        return self::getDefinition($protocol)["hashedBlockIds"];
    }

	/** @return array{minecraftVersion: string, chainedCommands: bool, hashedBlockIds: bool} */
	private static function getDefinition(int $protocol) : array{
		return self::VERSIONS[$protocol] ?? throw new \InvalidArgumentException("Unsupported Bedrock protocol " . $protocol);
	}
}
