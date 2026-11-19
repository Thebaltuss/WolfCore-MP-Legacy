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

namespace wolfcore\network\mcpe\cache;

use wolfcore\inventory\CreativeInventory;
use wolfcore\network\mcpe\convert\TypeConverter;
use wolfcore\network\mcpe\protocol\ProtocolVersions;
use wolfcore\network\mcpe\protocol\CreativeContentPacket;
use wolfcore\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use wolfcore\utils\SingletonTrait;
use function spl_object_id;

final class CreativeInventoryCache{
	use SingletonTrait;

	/**
	 * @var CreativeContentPacket[]
	 * @phpstan-var array<int, CreativeContentPacket>
	 */
	private array $caches = [];

	public function getCache(CreativeInventory $inventory, int $protocol = ProtocolVersions::BASE_PROTOCOL) : CreativeContentPacket{
		$id = spl_object_id($inventory) . ":" . $protocol;
		if(!isset($this->caches[$id])){
			$inventory->getDestructorCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$inventory->getContentChangedCallbacks()->add(function() use ($id) : void{
				unset($this->caches[$id]);
			});
			$this->caches[$id] = $this->buildCreativeInventoryCache($inventory, $protocol);
		}
		return $this->caches[$id];
	}

	/**
	 * Rebuild the cache for the given inventory.
	 */
	private function buildCreativeInventoryCache(CreativeInventory $inventory, int $protocol) : CreativeContentPacket{
		$entries = [];
		$typeConverter = TypeConverter::getForProtocol($protocol);
		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		foreach($inventory->getAll() as $k => $item){
			$entries[] = new CreativeContentEntry($k, $typeConverter->coreItemStackToNet($item));
		}

		return CreativeContentPacket::create($entries);
	}
}
