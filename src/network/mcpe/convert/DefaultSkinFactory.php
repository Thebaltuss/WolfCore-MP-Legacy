<?php

declare(strict_types=1);

namespace wolfcore\network\mcpe\convert;

use wolfcore\entity\Skin;
use function file_get_contents;
use function hexdec;
use function strlen;
use function str_replace;
use function substr;

/**
 * Provides real 64x64 Steve and Alex fallback textures without requiring GD.
 * A missing or malformed client skin must never become random pixels.
 */
final class DefaultSkinFactory{
	/** @var array<string, string> */
	private static array $textures = [];

	private function __construct(){}

	public static function fromIdentity(string $identity) : Skin{
		$uuid = str_replace("-", "", $identity);
		$lastNibble = substr($uuid, -1);
		$isAlex = $lastNibble !== "" && (hexdec($lastNibble) & 1) !== 0;
		$name = $isAlex ? "alex" : "steve";

		if(!isset(self::$textures[$name])){
			$path = __DIR__ . "/../../../../resources/skins/" . $name . ".rgba";
			$data = file_get_contents($path);
			if($data === false || strlen($data) !== 64 * 64 * 4){
				throw new \RuntimeException("Default skin texture is missing or corrupt: " . $path);
			}
			self::$textures[$name] = $data;
		}

		return new Skin(
			$isAlex ? "Standard_Alex" : "Standard_Steve",
			self::$textures[$name],
			"",
			$isAlex ? "geometry.humanoid.customSlim" : "geometry.humanoid.custom"
		);
	}
}
