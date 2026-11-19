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

namespace wolfcore\block\tile;

use wolfcore\block\inventory\HopperInventory;
use wolfcore\math\Vector3;
use wolfcore\nbt\tag\CompoundTag;
use wolfcore\world\World;

class Hopper extends Spawnable implements Container, Nameable{

	use ContainerTrait;
	use NameableTrait;

	private const TAG_TRANSFER_COOLDOWN = "TransferCooldown";

	private HopperInventory $inventory;
	/** Absolute server tick when this hopper may transfer another item. */
	private int $transferReadyTick = 0;

	public function getTransferCooldown() : int{
		return max(0, $this->transferReadyTick - $this->position->getWorld()->getServer()->getTick());
	}

	public function setTransferCooldown(int $ticks) : void{
		$this->transferReadyTick = $this->position->getWorld()->getServer()->getTick() + max(0, $ticks);
	}

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new HopperInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadItems($nbt);
		$this->loadName($nbt);

		$this->setTransferCooldown($nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 0));
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveItems($nbt);
		$this->saveName($nbt);

		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->getTransferCooldown());
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();

			parent::close();
		}
	}

	public function getDefaultName() : string{
		return "Hopper";
	}

	public function getInventory() : HopperInventory{
		return $this->inventory;
	}

	public function getRealInventory() : HopperInventory{
		return $this->inventory;
	}
}
