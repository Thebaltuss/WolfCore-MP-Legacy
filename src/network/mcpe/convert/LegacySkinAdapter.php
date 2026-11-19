<?php

/*
 * WolfCore-MP - Bedrock skin adapter.
 * Preserves Minecraft client skin metadata (including persona data and slim arms)
 * when forwarding the skin to other clients.
 */

declare(strict_types=1);

namespace wolfcore\network\mcpe\convert;

use wolfcore\entity\InvalidSkinException;
use wolfcore\entity\Skin;
use wolfcore\network\mcpe\protocol\types\skin\SkinData;
use wolfcore\network\mcpe\protocol\types\skin\SkinImage;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function str_contains;
use function strtolower;
use const JSON_THROW_ON_ERROR;

class LegacySkinAdapter implements SkinAdapter{
	/** @var \WeakMap<Skin, SkinData> */
	private \WeakMap $clientSkinData;

	public function __construct(){
		$this->clientSkinData = new \WeakMap();
	}

	public function toSkinData(Skin $skin) : SkinData{
		// SkinData contains much more than the legacy core Skin object.
		// Retaining the original avoids losing persona pieces, animation, slim arm size,
		// resource patch, PlayFab ID and other client-supplied appearance information.
		if(isset($this->clientSkinData[$skin])){
			return $this->clientSkinData[$skin];
		}

		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$geometryName = $skin->getGeometryName();
		if($geometryName === ""){
			$geometryName = "geometry.humanoid.custom";
		}

		// For skins created by plugins or read from NBT, infer Alex's slim arms
		// from the geometry name instead of always forcing wide Steve arms.
		$armSize = str_contains(strtolower($geometryName), "slim") ? SkinData::ARM_SIZE_SLIM : SkinData::ARM_SIZE_WIDE;
		return new SkinData(
			$skin->getSkinId(),
			"", // PlayFab ID unknown for custom/server-created skins
			json_encode(["geometry" => ["default" => $geometryName]], JSON_THROW_ON_ERROR),
			SkinImage::fromLegacy($skin->getSkinData()),
			[],
			$capeImage,
			$skin->getGeometryData(),
			armSize: $armSize
		);
	}

	public function fromSkinData(SkinData $data) : Skin{
		// A persona (Character Creator) is not equivalent to random RGBA pixels.
		// Keep its complete network appearance so that other clients can render it.
		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if(is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])){
			$geometryName = $resourcePatch["geometry"]["default"];
		}elseif($data->isPersona()){
			$geometryName = "geometry.humanoid.custom";
		}else{
			throw new InvalidSkinException("Missing geometry name field");
		}

		$capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();
		$skin = new Skin(
			$data->getSkinId(),
			$data->getSkinImage()->getData(),
			$capeData,
			$geometryName,
			$data->getGeometryData()
		);
		$this->clientSkinData[$skin] = $data;
		return $skin;
	}
}
